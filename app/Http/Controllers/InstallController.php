<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class InstallController extends Controller
{
    public function welcome()
    {
        $requirements = [
            'PHP Version >= 8.1'       => version_compare(phpversion(), '8.1.0', '>='),
            'BCMath Extension'         => extension_loaded('bcmath'),
            'Ctype Extension'          => extension_loaded('ctype'),
            'JSON Extension'           => extension_loaded('json'),
            'Mbstring Extension'       => extension_loaded('mbstring'),
            'OpenSSL Extension'        => extension_loaded('openssl'),
            'PDO Extension'            => extension_loaded('pdo'),
            'Tokenizer Extension'      => extension_loaded('tokenizer'),
            'XML Extension'            => extension_loaded('xml'),
            'Writable Storage'         => is_writable(storage_path()),
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
        $request->validate([
            'db_host'     => 'required',
            'db_port'     => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        $this->updateEnvironmentFile([
            'DB_HOST'     => $request->db_host,
            'DB_PORT'     => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
        ]);

        Artisan::call('config:clear');

        try {
            config([
                'database.connections.mysql.host'     => $request->db_host,
                'database.connections.mysql.port'     => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
            ]);
            DB::reconnect('mysql');
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot connect to database: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('install.migrations');
    }

    public function migrations()
    {
        return view('install.migrations');
    }

    public function runMigrations()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ignore_user_abort(true);

        try {
            \Illuminate\Support\Facades\Auth::logout();
            \Illuminate\Support\Facades\Session::flush();

            Artisan::call('migrate:fresh', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Database setup completed successfully.',
            ])->header('X-Accel-Buffering', 'no');

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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'is_super_admin'    => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('install.organization');
    }

    public function organization()
    {
        return view('install.organization');
    }

    public function storeOrganization(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug',
        ]);

        $organization = \App\Models\Organization::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->slug),
        ]);

        $user = User::first();
        if ($user) {
            $adminRole = Role::where('name', 'Admin')->first();
            $user->organizations()->attach($organization->id, ['role_id' => $adminRole?->id]);
        }

        return redirect()->route('install.network');
    }

    public function network()
    {
        return view('install.network');
    }

    public function finish(Request $request)
    {
        if ($request->network_type === 'domain' && $request->hasFile('ssl_cert') && $request->hasFile('ssl_key')) {
            $request->validate([
                'domain'   => 'required|string',
                'ssl_cert' => 'required|file',
                'ssl_key'  => 'required|file',
            ]);
            $sslPath = '/etc/nginx/ssl';
            if (!file_exists($sslPath)) { mkdir($sslPath, 0755, true); }
            $request->file('ssl_cert')->move($sslPath, 'orbitdocs.crt');
            $request->file('ssl_key')->move($sslPath, 'orbitdocs.key');
            $this->updateEnvironmentFile(['APP_URL' => 'https://' . $request->domain]);
        } else {
            if ($request->network_type === 'ip') {
                $this->updateEnvironmentFile(['APP_URL' => 'http://' . $request->getHost()]);
            }
        }

        // Mark installation complete. The entrypoint reads this file on container
        // restart to know it should run incremental migrations (update path).
        file_put_contents(storage_path('app/installed'), 'INSTALLED ON ' . now());

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return redirect()->route('login')->with('status', 'Installation complete! Please log in.');
    }

    protected function updateEnvironmentFile(array $data): void
    {
        $path = base_path('.env');
        if (!file_exists($path)) { return; }
        $env = file_get_contents($path);
        foreach ($data as $key => $value) {
            if (preg_match("/^{$key}=/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }
        file_put_contents($path, $env);
    }
}