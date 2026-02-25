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
        return view('install.welcome');
    }

    public function requirements()
    {
        $requirements = [
            'PHP Version >= 8.1.0' => phpversion() >= '8.1.0',
            'BCMath Extension' => extension_loaded('bcmath'),
            'Ctype Extension' => extension_loaded('ctype'),
            'JSON Extension' => extension_loaded('json'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            '.env Writable' => is_writable(base_path('.env')),
            'storage Writable' => is_writable(storage_path()),
            'bootstrap/cache Writable' => is_writable(base_path('bootstrap/cache')),
        ];

        return view('install.requirements', compact('requirements'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function status()
    {
        $path = storage_path('app/install_progress.json');
        if (file_exists($path)) {
            return response()->json(json_decode(file_get_contents($path)));
        }
        return response()->json(['progress' => 0, 'status' => 'Starting...']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'admin_name' => 'required',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8',
        ]);

        try {
            $progressPath = storage_path('app/install_progress.json');
            file_put_contents($progressPath, json_encode(['progress' => 10, 'status' => 'Updating environment...']));

            // Update .env
            $this->updateEnvironmentFile([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);

            file_put_contents($progressPath, json_encode(['progress' => 30, 'status' => 'Clearing configuration...']));
            // Clear config cache
            Artisan::call('config:clear');

            file_put_contents($progressPath, json_encode(['progress' => 50, 'status' => 'Running migrations... This might take a moment.']));
            // Run migrations
            Artisan::call('migrate:fresh', ['--force' => true]);

            file_put_contents($progressPath, json_encode(['progress' => 80, 'status' => 'Creating administrator...']));
            // Create admin user
            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'is_super_admin' => true,
            ]);

            file_put_contents($progressPath, json_encode(['progress' => 100, 'status' => 'Complete!']));

            // Mark as installed
            file_put_contents(storage_path('app/installed'), 'INSTALLED ON ' . now());
            @unlink($progressPath);

            return redirect()->route('login')->with('status', 'Installation Completed Successfully!');
        }
        catch (\Exception $e) {
            @unlink($progressPath);
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
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
                }
                else {
                    // Append if not exists
                    $currentEnv .= "\n{$key}=\"{$value}\"";
                }
            }

            file_put_contents($path, $currentEnv);
        }
    }
}
