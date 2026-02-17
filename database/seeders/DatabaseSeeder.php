<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AssetType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run RBAC Seeder (Creates Roles & Permissions)
        $this->call([
            RolesPermissionsSeeder::class ,
            ITGlueAssetSeeder::class ,
            // ITGlueDemoDataSeeder::class, // Optional: Comment out if not needed for production
        ]);

        // Default Admin User - SKIPPED FOR INSTALLER FLOW
        // $user = User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@orbitdocs.com',
        //     'password' => Hash::make('password'),
        // ]);

        // Default Organization - SKIPPED FOR INSTALLER FLOW
        // $org = Organization::create([
        //     'name' => 'Demo MSP',
        //     'slug' => 'demo-msp',
        // ]);

        // Assign User to Org with Admin Role - SKIPPED
        // $adminRole = Role::where('name', 'Admin')->first();
        // $org->users()->attach($user->id, ['role_id' => $adminRole->id]);

        // Asset Types
        $types = ['Server', 'Workstation', 'Firewall', 'Switch', 'License', 'Domain'];
        foreach ($types as $type) {
            AssetType::create(['name' => $type]);
        }
    }
}
