<?php

namespace Database\Seeders;

use App\Models\ChatPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ChatPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create chat permissions
        $permissions = [
            'view chat',
            'send messages',
            'manage chat',
            'block users',
            'view all conversations',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Create default chat permissions for role-to-role communication
        $this->createRoleToChatPermissions();
    }

    /**
     * Assign chat permissions to existing roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin - all permissions
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'view chat',
                'send messages',
                'manage chat',
                'block users',
                'view all conversations',
            ]);
        }

        // Admin - management permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                'view chat',
                'send messages',
                'manage chat',
                'block users',
            ]);
        }

        // Doctor - chat with partners and admin
        $doctor = Role::where('name', 'doctor')->first();
        if ($doctor) {
            $doctor->givePermissionTo([
                'view chat',
                'send messages',
            ]);
        }

        // Partner - chat with doctors
        $partner = Role::where('name', 'partner')->first();
        if ($partner) {
            $partner->givePermissionTo([
                'view chat',
                'send messages',
            ]);
        }
    }

    /**
     * Create default role-to-role chat permissions
     */
    private function createRoleToChatPermissions(): void
    {
        // Define all roles (lowercase to match RolePermissionSeeder)
        $roles = ['super-admin', 'admin', 'doctor', 'partner'];

        foreach ($roles as $fromRole) {
            foreach ($roles as $toRole) {
                // Super Admin can chat with everyone
                if ($fromRole === 'super-admin') {
                    ChatPermission::updateOrCreate(
                        ['from_role' => $fromRole, 'to_role' => $toRole],
                        ['can_initiate' => true, 'can_receive' => true]
                    );
                    continue;
                }

                // Admin can chat with everyone
                if ($fromRole === 'admin') {
                    ChatPermission::updateOrCreate(
                        ['from_role' => $fromRole, 'to_role' => $toRole],
                        ['can_initiate' => true, 'can_receive' => true]
                    );
                    continue;
                }

                // Doctor can chat with admin and partners
                if ($fromRole === 'doctor') {
                    if (in_array($toRole, ['admin', 'super-admin', 'partner'])) {
                        ChatPermission::updateOrCreate(
                            ['from_role' => $fromRole, 'to_role' => $toRole],
                            ['can_initiate' => true, 'can_receive' => true]
                        );
                    } else {
                        ChatPermission::updateOrCreate(
                            ['from_role' => $fromRole, 'to_role' => $toRole],
                            ['can_initiate' => false, 'can_receive' => true]
                        );
                    }
                    continue;
                }

                // Partner can chat with doctors
                if ($fromRole === 'partner') {
                    if (in_array($toRole, ['doctor'])) {
                        ChatPermission::updateOrCreate(
                            ['from_role' => $fromRole, 'to_role' => $toRole],
                            ['can_initiate' => true, 'can_receive' => true]
                        );
                    } else {
                        ChatPermission::updateOrCreate(
                            ['from_role' => $fromRole, 'to_role' => $toRole],
                            ['can_initiate' => false, 'can_receive' => true]
                        );
                    }
                }
            }
        }

        $this->command->info('Chat permissions created successfully!');
    }
}
