<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'manage_users',
            'manage_all_forms',
            'manage_own_forms',
            'publish_forms',
            'view_all_responses',
            'view_own_responses',
            'export_responses',
            'delete_responses',
            'view_audit_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles and Assign Permissions
        
        // Super Admin
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super admin gets all permissions via a gate check or direct assignment
        $superAdminRole->syncPermissions($permissions);

        // Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions([
            'manage_all_forms',
            'publish_forms',
            'view_all_responses',
            'export_responses',
            'delete_responses',
        ]);

        // Creator
        $creatorRole = Role::firstOrCreate(['name' => 'Creator']);
        $creatorRole->syncPermissions([
            'manage_own_forms',
            'publish_forms',
            'view_own_responses',
            'export_responses',
            'delete_responses',
        ]);

        // Viewer
        $viewerRole = Role::firstOrCreate(['name' => 'Viewer']);
        $viewerRole->syncPermissions([
            'view_own_responses',
            'export_responses',
        ]);
    }
}
