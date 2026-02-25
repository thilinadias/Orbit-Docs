<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    private function statusFile(): string
    {
        return storage_path('app/migration_status.json');
    }

    public function welcome()
    {
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
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required'
        ]);
        
        $this->updateEnvironmentFile([
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password
        ]);

        Artisan::call('config:clear');

        try {
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password
            ]);
            DB::reconnect('mysql');
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return back()->with('error', 'Cannot connect: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('install.migrations');
    }

    public function migrations()
    {
        return view('install.migrations');
    }

    public function runMigrations()
    {
        if (file_exists($this->statusFile())) {
            $existing = json_decode(file_get_contents($this->statusFile()), true);
            if (($existing['status'] ?? '') === 'running' && (time() - strtotime($existing['started'] ?? '')) < 600) {
                return response()->json(['success' => true, 'message' => 'InProgress']);
            }
        }

        file_put_contents($this->statusFile(), json_encode([
            'status' => 'running',
            'step' => 'Preparing...',
            'progress' => 15,
            'started' => date('c')
        ]));

        $sf = $this->statusFile();
        $now = date('c');
        $script = base_path('database/migration_runner.sh');
        
        exec("nohup /usr/bin/bash {$script} \"{$sf}\" \"{$now}\" > /tmp/migration_runner.log 2>&1 &");

        return response()->json(['success' => true, 'message' => 'Started']);
    }

    public function migrationStatus()
    {
        $file = $this->statusFile();
        if (!file_exists($file)) {
            return response()->json(['status' => 'not_started', 'step' => 'Waiting...']);
        }
        return response()->json(json_decode(file_get_contents($file), true) ?? ['status' => 'error', 'step' => 'ReadError']);
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_super_admin' => true,
            'email_verified_at' => now()
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
            'name' => 'required',
            'slug' => 'required|unique:organizations,slug'
        ]);

        $org = \App\Models\Organization::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug)
        ]);

        $user = User::first();
        if ($user) {
            $user->organizations()->attach($org->id, [
                'role_id' => Role::where('name', 'Admin')->first()?->id
            ]);
        }

        return redirect()->route('install.network');
    }

    public function network()
    {
        return view('install.network');
    }

    public function finish(Request $request)
    {
        if ($request->network_type === 'domain') {
            $this->updateEnvironmentFile(['APP_URL' => 'https://' . $request->domain]);
        }
        
        file_put_contents(storage_path('app/installed'), date('c'));
        @unlink($this->statusFile());
        Artisan::call('optimize:clear');

        return redirect()->route('login');
    }

    protected function updateEnvironmentFile(array $data)
    {
        $path = base_path('.env');
        if (!file_exists($path)) return;

        $env = file_get_contents($path);
        foreach ($data as $k => $v) {
            if (preg_match("/^{$k}=/m", $env)) {
                $env = preg_replace("/^{$k}=.*/m", "{$k}={$v}", $env);
            } else {
                $env .= "\n{$k}={$v}";
            }
        }
        file_put_contents($path, $env);
    }
}
