<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@admin.com';
        $password = 'password';

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Admin User', 'password' => Hash::make($password)]
        );

        // attach admin role if roles exist
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('admin');
            } catch (\Throwable $e) {
                // swallow if role package not configured yet
            }
        }

        $this->command->info("Admin user ensured: {$email} / password: {$password}");
    }
}
