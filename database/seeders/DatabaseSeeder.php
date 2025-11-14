<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete System Setup...');
        $this->command->newLine();

        // 1. Roles and Permissions
        $this->command->info('📋 Step 1: Creating roles and permissions...');
        $this->call(RolePermissionSeeder::class);
        $this->command->newLine();

        // Ensure an admin user exists (email: admin@admin.com / password: password)
        $this->command->info('👤 Ensuring admin user exists...');
        if (!class_exists(\Database\Seeders\AdminUserSeeder::class) && file_exists(database_path('seeders/AdminUserSeeder.php'))) {
            require_once database_path('seeders/AdminUserSeeder.php');
        }

        if (class_exists(\Database\Seeders\AdminUserSeeder::class)) {
            // Prefer container-aware call, but fall back to direct run if container can't resolve
            try {
                $this->call(AdminUserSeeder::class);
            } catch (\Throwable $e) {
                // Directly instantiate and run as fallback
                (new \Database\Seeders\AdminUserSeeder())->run();
            }
        } else {
            $this->command->warn('AdminUserSeeder class not found; skipping creation of admin user.');
        }
        $this->command->newLine();

        // 2. Chat Permissions
        $this->command->info('💬 Step 2: Setting up chat permissions...');
        $this->call(ChatPermissionSeeder::class);
        $this->command->newLine();

        // 3. Booking Permissions
        $this->command->info('📅 Step 3: Setting up booking permissions...');
        $this->call(BookingPermissionSeeder::class);
        $this->command->newLine();

    // 4. Test Data (Specializations, Users, Doctors, Partners, Children, Appointments)
    $this->command->info('🧪 Step 4: Creating test data...');
    $this->call(BookingTestSeeder::class);
    $this->command->newLine();

    // 4.5. Dysgraphia Specialists
    $this->command->info('🧪 Step 4.5: Creating Dysgraphia specialists for booking system...');
    $this->call(DysgraphiaSpecialistSeeder::class);
    $this->command->newLine();

    // 5. Algeria (countries/states/cities)
        $this->command->info('🌍 Step 5: Seeding Algeria (countries, states, cities)...');
            // Ensure the class is autoloadable in environments where composer dump-autoload hasn't been run yet
            if (!class_exists(\Database\Seeders\AlgeriaSeeder::class) && file_exists(database_path('seeders/AlgeriaSeeder.php'))) {
                require_once database_path('seeders/AlgeriaSeeder.php');
            }

            if (class_exists(\Database\Seeders\AlgeriaSeeder::class)) {
                try {
                    $this->call(AlgeriaSeeder::class);
                } catch (\Throwable $e) {
                    (new \Database\Seeders\AlgeriaSeeder())->run();
                }
            } else {
                $this->command->warn('AlgeriaSeeder class not found; skipping Algeria import.');
            }
    $this->command->newLine();

        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✅ Complete System Setup Finished!');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        $this->displaySystemSummary();
    }

    /**
     * Display system summary
     */
    private function displaySystemSummary(): void
    {
        $this->command->info('📊 SYSTEM OVERVIEW:');
        $this->command->newLine();
        
        $this->command->info('👥 Roles Created:');
        $this->command->line('   • super-admin - Full system access');
        $this->command->line('   • admin       - Add/manage doctors, view system');
        $this->command->line('   • doctor      - Configure schedule, provide services, chat with partners');
        $this->command->line('   • partner     - Book appointments for children, chat with doctors');
        $this->command->newLine();

        $this->command->info('🔑 Test Login Credentials (password: password):');
        $this->command->line('   Super Admin: superadmin@test.com');
        $this->command->line('   Admin:       admin@test.com');
        $this->command->line('   Doctors:     dr.smith@test.com, dr.johnson@test.com, dr.williams@test.com');
        $this->command->line('   Partners:    partner1@test.com, partner2@test.com');
        $this->command->newLine();

        $this->command->info('✨ System Features:');
        $this->command->line('   ✓ Admin can add doctors (name + email)');
        $this->command->line('   ✓ Doctors can configure their schedule');
        $this->command->line('   ✓ Doctors can chat with partners and admin');
        $this->command->line('   ✓ Partners can add children with medical notes');
        $this->command->line('   ✓ Partners can book appointments for children');
        $this->command->line('   ✓ Partners can chat with doctors');
        $this->command->line('   ✓ Appointments linked to specific children');
        $this->command->newLine();

        $this->command->info('🎯 Next Steps:');
        $this->command->line('   1. Start development server: php artisan serve');
        $this->command->line('   2. Start Reverb server: php artisan reverb:start');
        $this->command->line('   3. Build assets: npm run dev');
        $this->command->line('   4. Login and test the system!');
        $this->command->newLine();
    }
}
