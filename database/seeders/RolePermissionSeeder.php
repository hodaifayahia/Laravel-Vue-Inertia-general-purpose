<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            
            // Role permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Permission permissions
            'view permissions',
            'assign permissions',
            
            // Dashboard
            'view dashboard',
            
            // Children permissions
            'view children',
            'create children',
            'edit children',
            'delete children',
            'view all children',
            
            // Sidebar access permissions
            'view dashboard sidebar',
            'view users sidebar',
            'view roles sidebar',
            'view permissions sidebar',
            'view children sidebar',
            'view locations sidebar',
            'view settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

                // Admin - can add doctors and manage system
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view dashboard',
            'view dashboard sidebar',
            'view users sidebar',
            'view roles sidebar',
            'view locations sidebar',
            'view all children',
            'view children sidebar',
            'view settings',
        ]);

        // Doctor - can configure schedule and manage appointments
        $doctor = Role::firstOrCreate(['name' => 'doctor']);
        $doctor->givePermissionTo([
            'view dashboard',
            'view dashboard sidebar',
        ]);
        
        // Partner - can book appointments and manage children
        $partner = Role::firstOrCreate(['name' => 'partner']);
        $partner->givePermissionTo([
            'view dashboard',
            'view dashboard sidebar',
            'view children',
            'create children',
            'edit children',
            'delete children',
            'view children sidebar',
        ]);

        // Assign super-admin role to the first user if exists
        $firstUser = \App\Models\User::first();
        if ($firstUser && !$firstUser->hasRole('super-admin')) {
            $firstUser->assignRole('super-admin');
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
