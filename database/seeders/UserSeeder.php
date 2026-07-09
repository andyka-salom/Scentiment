<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch existing roles
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $creatorRole = Role::where('name', 'Creator')->first();
        $viewerRole = Role::where('name', 'Viewer')->first();

        // 2. Create Users and Assign Roles
        
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@flow.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        if ($superAdminRole) {
            $superAdmin->syncRoles($superAdminRole);
        }

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@flow.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        if ($adminRole) {
            $admin->syncRoles($adminRole);
        }

        // Creator
        $creator = User::firstOrCreate(
            ['email' => 'creator@flow.com'],
            [
                'name' => 'Creator User',
                'password' => Hash::make('password'),
            ]
        );
        if ($creatorRole) {
            $creator->syncRoles($creatorRole);
        }

        // Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@flow.com'],
            [
                'name' => 'Viewer User',
                'password' => Hash::make('password'),
            ]
        );
        if ($viewerRole) {
            $viewer->syncRoles($viewerRole);
        }
    }
}
