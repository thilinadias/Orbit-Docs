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
    /**
     * Path to the migration status file used for async polling.
     * The web request starts artisan in the background and returns immediately;
     * the frontend polls migrationStatus() until this file says "done" or "error".
     */
    private function statusFile(): string
    {
        return storage_path('app/migration_status.json');
    }

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
        } catch (\Throwable $e) {
            return back()->with('error', 'Cannot connect: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('install.migrations');
    }

    public function migrations()
    {
        return view('install.migrations');
    }

    /**
     * Start a background migration process and return IMMEDIATELY.
     *
     * WHY ASYNC?
     *   Nginx has a fastcgi_read_timeout (often 60s). If the PHP-FPM worker
     *   takes longer than that (migrate:fresh on 35+ tables can take 90-120s),
     *   Nginx returns 504 Gateway Timeout to the browser BEFORE PHP finishes.
     *   No amount of set_time_limit() in PHP helps because the timeout is in
     *   the Nginx->PHP-FPM connection layer.
     *
     *   By spawning artisan as a background process and returning immediately,
     *   the web request completes in <1 second. The frontend polls
     *   migrationStatus() every 2 seconds to check progress.
     */
    public function runMigrations()
    {
        // Write initial status
        file_put_contents($this->statusFile(), json_encode([
            'status'  => 'running',
            'step'    => 'Starting database setup...',
            'started' => date('c'),
        ]));

        // Build the background command.
        // This script runs migrate:fresh, then db:seed, writing status after each.
        $php     = '/usr/local/bin/php';
        $artisan = base_path('artisan');
        $sf      = $this->statusFile();

        // Shell script that runs migrate:fresh, checks result, runs seed, writes status
        $script = <<<BASH
{$php} {$artisan} migrate:fresh --force --no-interaction > /tmp/migrate_output.txt 2>&1
MIGRATE_EXIT=\$?
if [ \$MIGRATE_EXIT -ne 0 ]; then
    OUTPUT=\$(cat /tmp/migrate_output.txt)
    echo '{"status":"error","step":"migrate:fresh failed","message":"'\$(echo \$OUTPUT | tr '"' "'" | tr '\n' ' ')'"}'  > {$sf}
    exit 1
fi
{$php} {$artisan} db:seed --force --no-interaction > /tmp/seed_output.txt 2>&1
SEED_EXIT=\$?
if [ \$SEED_EXIT -ne 0 ]; then
    OUTPUT=\$(cat /tmp/seed_output.txt)
    echo '{"status":"error","step":"db:seed failed","message":"'\$(echo \$OUTPUT | tr '"' "'" | tr '\n' ' ')'"}'  > {$sf}
    exit 1
fi
echo '{"status":"done","step":"Complete"}' > {$sf}
BASH;

        // Launch in background (nohup + &), completely detached from this process
        $escaped = base64_encode($script);
        exec("echo '{$escaped}' | base64 -d | nohup bash > /tmp/migration_runner.log 2>&1 &");

        return response()->json([
            'success' => true,
            'message' => 'Migration started in background. Polling for status...',
        ]);
    }

    /**
     * Poll endpoint — frontend calls this every 2 seconds.
     */
    public function migrationStatus()
    {
        $file = $this->statusFile();

        if (!file_exists($file)) {
            return response()->json([
                'status'  => 'not_started',
                'step'    => 'Waiting to start...',
                'message' => null,
            ]);
        }

        $data = json_decode(file_get_contents($file), true) ?? [
            'status'  => 'error',
            'step'    => 'Could not read status file',
            'message' => 'Corrupted status file',
        ];

        return response()->json($data);
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

        file_put_contents(storage_path('app/installed'), 'INSTALLED ON ' . now());
        @unlink($this->statusFile());

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