<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-users',
            'manage-categories',
            'view-reports',
            'manage-availability',
            'book-trials',
            'manage-clients',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $counsellor = Role::firstOrCreate(['name' => 'counsellor']);
        $counsellor->syncPermissions(['manage-clients', 'book-trials']);

        $trainer = Role::firstOrCreate(['name' => 'trainer']);
        $trainer->syncPermissions(['manage-availability', 'book-trials']);
    }
}
