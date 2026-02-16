<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    public function welcome()
    {
        // specific check for requirements
        $requirements = [
            'PHP Version >= 8.1' => version_compare(phpversion(), '8.1.0', '>='),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Ctype Extension' => extension_loaded('ctype'),
            'JSON Extension' => extension_loaded('json'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'Writable Storage' => is_writable(storage_path()),
            'Writable Bootstrap Cache' => is_writable(base_path('bootstrap/cache')),
        ];

        $allMet = !in_array(false, $requirements);

        return view('install.welcome', compact('requirements', 'allMet'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function storeDatabase(Request $request)
    {
        // Logic to update .env and test connection
        // For simplicity in this first pass, we might just validate inputs
        // In a real Docker env, we might be writing to a mounted .env or setting env vars.
        // Since we mount .env, we can overwrite it.

        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable', // Password can be empty
        ]);

        $this->updateEnvironmentFile([
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
        ]);

        // Clear config cache to ensure new values are used
        Artisan::call('config:clear');

        // Test connection
        try {
            // We force a new connection because the config is already loaded into memory
             // Setting config at runtime for testing
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
            ]);
            DB::reconnect('mysql');
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return back()->with('error', 'Could not connect to database: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('install.migrations');
    }

    public function migrations()
    {
        return view('install.migrations');
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
            // Output might be captured if we want to show it
            return response()->json(['success' => true, 'message' => 'Migrations and Seeding completed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        // Create Admin
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_super_admin' => true, 
            'email_verified_at' => now(),
        ]);
        
        // Assign Role if roles are seeded?
        // Assuming RolesPermissionsSeeder didn't assign super admin to a specific user initially, or we attach 'Super Admin' role.
        // But is_super_admin column handles it mostly.

        return redirect()->route('install.network');
    }

    public function network()
    {
        return view('install.network');
    }

    public function finish(Request $request)
    {
        // Handle Network/SSL Configuration
        if ($request->network_type === 'domain' && $request->hasFile('ssl_cert') && $request->hasFile('ssl_key')) {
             // Validate files
            $request->validate([
                'domain' => 'required|string',
                'ssl_cert' => 'required|file',
                'ssl_key' => 'required|file',
            ]);

            // Define paths in the shared volume
            $sslPath = '/etc/nginx/ssl'; // This is where we mapped orbitdocs_ssl in docker-compose for App container
            
            // Ensure directory exists (it should via docker volume, but good to check)
            if (!file_exists($sslPath)) {
                mkdir($sslPath, 0755, true);
            }

            // Save files
            $request->file('ssl_cert')->move($sslPath, 'orbitdocs.crt');
            $request->file('ssl_key')->move($sslPath, 'orbitdocs.key');

            // Update APP_URL in .env
            $this->updateEnvironmentFile(['APP_URL' => 'https://' . $request->domain]);

            // NOTE: We cannot easily restart Nginx from here. 
            // The user will see a "Restart Required" message or instructions in the dashboard if we want.
            // But for now, we just save the files. 
        } else {
             // Update APP_URL in .env to IP if that's what we have (or just keep as is)
             $currentIp = $request->getHost();
             if ($request->network_type === 'ip') {
                 $this->updateEnvironmentFile(['APP_URL' => 'http://' . $currentIp]);
             }
        }

        // Finalize installation
        file_put_contents(storage_path('app/installed'), 'INSTALLED ON ' . now());
        
        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return redirect()->route('login')->with('status', 'Installation Completed! Please log in with your super admin credentials.');
    }

    protected function updateEnvironmentFile($data)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $currentEnv = file_get_contents($path);
            foreach ($data as $key => $value) {
                // Check if key exists
                if (preg_match("/^{$key}=/m", $currentEnv)) {
                     $currentEnv = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $currentEnv);
                } else {
                    // Append if not exists
                    $currentEnv .= "\n{$key}=\"{$value}\"";
                }
            }
            file_put_contents($path, $currentEnv);
        }
    }
}
