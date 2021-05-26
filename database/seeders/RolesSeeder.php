<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = Permission::create([
            'name' => '*',
            'display_name' => 'all',
        ]);

        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $permission->assignRole($role);

        $role = Role::create([
            'name' => 'customer',
            'display_name' => 'Customer',
        ]);

        $permission->assignRole($role);
    }
}
