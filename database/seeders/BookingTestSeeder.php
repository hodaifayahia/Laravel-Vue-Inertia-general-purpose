<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Specialization;
use App\Models\ProviderProfile;
use App\Models\ProviderSchedule;
use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BookingTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Booking System Test Seeder...');

        // Create test specializations
        $this->command->info('📋 Creating specializations...');
        $specializations = $this->createSpecializations();

        // Create test users with different roles
        $this->command->info('👥 Creating test users...');
        $users = $this->createTestUsers();

        // Create provider profiles
        $this->command->info('👨‍⚕️ Creating provider profiles...');
        $providers = $this->createProviderProfiles($users, $specializations);

        // Create provider schedules
        $this->command->info('📅 Creating provider schedules...');
        $this->createProviderSchedules($providers);

        // Create test appointments
        $this->command->info('📝 Creating test appointments...');
        $this->createTestAppointments($users, $providers);

        $this->command->info('✅ Booking System Test Seeder completed successfully!');
        $this->command->newLine();
        $this->displayTestUsers();
    }

    /**
     * Create test specializations
     */
    private function createSpecializations(): array
    {
        $specs = [
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'description' => 'Heart and cardiovascular system specialists',
                'icon' => '❤️',
            ],
            [
                'name' => 'Dermatology',
                'slug' => 'dermatology',
                'description' => 'Skin, hair, and nail care specialists',
                'icon' => '🩺',
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'description' => 'Child health and development specialists',
                'icon' => '👶',
            ],
            [
                'name' => 'Orthopedics',
                'slug' => 'orthopedics',
                'description' => 'Bone, joint, and muscle specialists',
                'icon' => '🦴',
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'description' => 'Brain and nervous system specialists',
                'icon' => '🧠',
            ],
        ];

        $specializations = [];
        foreach ($specs as $spec) {
            $specializations[] = Specialization::firstOrCreate(
                ['slug' => $spec['slug']],
                [
                    'name' => $spec['name'],
                    'description' => $spec['description'],
                    'icon' => $spec['icon'],
                    'is_active' => true,
                ]
            );
        }

        return $specializations;
    }

    /**
     * Create test users with different roles
     */
    private function createTestUsers(): array
    {
        $users = [];

        // Super Admin - can do everything
        $users['super_admin'] = User::firstOrCreate(
            ['email' => 'superadmin@test.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $users['super_admin']->assignRole('super-admin');

        // Admin - can manage bookings
        $users['admin'] = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $users['admin']->assignRole('admin');

        // Doctor 1 - Cardiologist (can provide services)
        $users['doctor1'] = User::firstOrCreate(
            ['email' => 'dr.smith@test.com'],
            [
                'name' => 'Dr. John Smith',
                'password' => Hash::make('password'),
            ]
        );
        $users['doctor1']->assignRole('doctor');

        // Doctor 2 - Dermatologist (can provide services)
        $users['doctor2'] = User::firstOrCreate(
            ['email' => 'dr.johnson@test.com'],
            [
                'name' => 'Dr. Sarah Johnson',
                'password' => Hash::make('password'),
            ]
        );
        $users['doctor2']->assignRole('doctor');

        // Doctor 3 - Pediatrician (can provide services)
        $users['doctor3'] = User::firstOrCreate(
            ['email' => 'dr.williams@test.com'],
            [
                'name' => 'Dr. Emily Williams',
                'password' => Hash::make('password'),
            ]
        );
        $users['doctor3']->assignRole('doctor');

        // Partner 1 - can book appointments for children
        $users['partner1'] = User::firstOrCreate(
            ['email' => 'partner1@test.com'],
            [
                'name' => 'Alice Partner',
                'password' => Hash::make('password'),
            ]
        );
        $users['partner1']->assignRole('partner');

        // Partner 2 - can book appointments for children
        $users['partner2'] = User::firstOrCreate(
            ['email' => 'partner2@test.com'],
            [
                'name' => 'Bob Partner',
                'password' => Hash::make('password'),
            ]
        );
        $users['partner2']->assignRole('partner');

        return $users;
    }

    /**
     * Create provider profiles
     */
    private function createProviderProfiles(array $users, array $specializations): array
    {
        $providers = [];

        // Get some sample provinces and cities for location data
        $algiersProvince = \App\Models\Province::where('code', '16')->first(); // Algiers
        $oranProvince = \App\Models\Province::where('code', '31')->first(); // Oran
        $constantineProvince = \App\Models\Province::where('code', '25')->first(); // Constantine

        $algiersCity = \App\Models\City::where('province_id', $algiersProvince?->id)->first();
        $oranCity = \App\Models\City::where('province_id', $oranProvince?->id)->first();
        $constantineCity = \App\Models\City::where('province_id', $constantineProvince?->id)->first();

        // Doctor 1 - Cardiologist (Algiers)
        $providers['doctor1'] = ProviderProfile::updateOrCreate(
            ['user_id' => $users['doctor1']->id],
            [
                'specialization_id' => $specializations[0]->id, // Cardiology
                'province_id' => $algiersProvince?->id,
                'city_id' => $algiersCity?->id,
                'bio' => 'Experienced cardiologist with over 15 years of practice. Specialized in preventive cardiology and heart disease management. Committed to providing personalized care for each patient.',
                'years_experience' => 15,
                'slot_duration' => 45,
                'is_available' => true,
                'title' => 'Dr.',
                'license_number' => 'MED-ALG-2023-001',
                'qualifications' => json_encode(['MD Cardiology', 'FACC', 'Board Certified']),
                'languages' => json_encode(['Arabic', 'French', 'English']),
                'phone' => '+213-555-0101',
                'office_address' => '123 Cardiology Center, Algiers Medical District',
                'clinic_name' => 'Algiers Heart Institute',
                'consultation_fee' => 150.00,
                'advance_booking_days' => 30,
                'services_offered' => json_encode(['Cardiac Consultation', 'ECG', 'Echocardiography', 'Stress Testing']),
                'education' => json_encode([
                    ['degree' => 'MD in Cardiology', 'institution' => 'Algiers Medical University', 'year' => '2008'],
                    ['degree' => 'Residency in Internal Medicine', 'institution' => 'Central Hospital Algiers', 'year' => '2011']
                ]),
                'awards' => json_encode([
                    ['title' => 'Best Cardiologist Award', 'year' => '2020'],
                    ['title' => 'Excellence in Patient Care', 'year' => '2018']
                ]),
                'website' => 'https://algiersheart.com',
                'social_links' => json_encode([
                    'facebook' => 'https://facebook.com/algiersheart',
                    'linkedin' => 'https://linkedin.com/in/drjohnsmith'
                ]),
            ]
        );

        // Doctor 2 - Dermatologist (Oran)
        $providers['doctor2'] = ProviderProfile::updateOrCreate(
            ['user_id' => $users['doctor2']->id],
            [
                'specialization_id' => $specializations[1]->id, // Dermatology
                'province_id' => $oranProvince?->id,
                'city_id' => $oranCity?->id,
                'bio' => 'Board-certified dermatologist specializing in medical and cosmetic dermatology. Expert in skin cancer detection, acne treatment, and anti-aging procedures. Passionate about helping patients achieve healthy skin.',
                'years_experience' => 10,
                'slot_duration' => 30,
                'is_available' => true,
                'title' => 'Dr.',
                'license_number' => 'DER-ORN-2022-045',
                'qualifications' => json_encode(['MD Dermatology', 'Board Certified Dermatologist', 'Laser Specialist']),
                'languages' => json_encode(['Arabic', 'French', 'English']),
                'phone' => '+213-555-0202',
                'office_address' => '456 Skin Care Clinic, Oran Dermatology Center',
                'clinic_name' => 'Oran Skin & Beauty Clinic',
                'consultation_fee' => 120.00,
                'advance_booking_days' => 21,
                'services_offered' => json_encode(['Dermatology Consultation', 'Acne Treatment', 'Skin Cancer Screening', 'Laser Therapy']),
                'education' => json_encode([
                    ['degree' => 'MD in Dermatology', 'institution' => 'Oran Medical School', 'year' => '2013'],
                    ['degree' => 'Fellowship in Cosmetic Dermatology', 'institution' => 'International Dermatology Institute', 'year' => '2016']
                ]),
                'awards' => json_encode([
                    ['title' => 'Young Dermatologist of the Year', 'year' => '2019']
                ]),
                'website' => 'https://oranskincare.com',
                'social_links' => json_encode([
                    'instagram' => 'https://instagram.com/oranskincare',
                    'linkedin' => 'https://linkedin.com/in/drsarahjohnson'
                ]),
            ]
        );

        // Doctor 3 - Pediatrician (Constantine)
        $providers['doctor3'] = ProviderProfile::updateOrCreate(
            ['user_id' => $users['doctor3']->id],
            [
                'specialization_id' => $specializations[2]->id, // Pediatrics
                'province_id' => $constantineProvince?->id,
                'city_id' => $constantineCity?->id,
                'bio' => 'Caring pediatrician dedicated to children\'s health and well-being. Experienced in newborn care, vaccinations, and developmental monitoring. Creating a comfortable environment for young patients and their families.',
                'years_experience' => 8,
                'slot_duration' => 30,
                'is_available' => true,
                'title' => 'Dr.',
                'license_number' => 'PED-CON-2021-078',
                'qualifications' => json_encode(['MD Pediatrics', 'Board Certified Pediatrician', 'Neonatal Specialist']),
                'languages' => json_encode(['Arabic', 'French', 'English']),
                'phone' => '+213-555-0303',
                'office_address' => '789 Children\'s Health Center, Constantine Pediatric District',
                'clinic_name' => 'Constantine Children\'s Clinic',
                'consultation_fee' => 100.00,
                'advance_booking_days' => 14,
                'services_offered' => json_encode(['Pediatric Consultation', 'Vaccinations', 'Developmental Checkups', 'Newborn Care']),
                'education' => json_encode([
                    ['degree' => 'MD in Pediatrics', 'institution' => 'Constantine University Medical School', 'year' => '2015'],
                    ['degree' => 'Pediatric Residency', 'institution' => 'Children\'s Hospital Constantine', 'year' => '2018']
                ]),
                'awards' => json_encode([
                    ['title' => 'Pediatrician of the Year', 'year' => '2022']
                ]),
                'website' => 'https://constantinepediatrics.com',
                'social_links' => json_encode([
                    'facebook' => 'https://facebook.com/constantinepediatrics',
                    'twitter' => 'https://twitter.com/dr_emily_w'
                ]),
            ]
        );

        return $providers;
    }

    /**
     * Create provider schedules
     */
    private function createProviderSchedules(array $providers): void
    {
        // Doctor 1 Schedule - Available Monday to Friday
        $days = [
            ['day' => 1, 'start' => '09:00', 'end' => '17:00'], // Monday
            ['day' => 2, 'start' => '09:00', 'end' => '17:00'], // Tuesday
            ['day' => 3, 'start' => '09:00', 'end' => '17:00'], // Wednesday
            ['day' => 4, 'start' => '10:00', 'end' => '18:00'], // Thursday
            ['day' => 5, 'start' => '09:00', 'end' => '15:00'], // Friday
        ];

        foreach ($days as $day) {
            ProviderSchedule::firstOrCreate(
                [
                    'provider_profile_id' => $providers['doctor1']->id,
                    'day_of_week' => $day['day'],
                ],
                [
                    'start_time' => $day['start'],
                    'end_time' => $day['end'],
                    'is_available' => true,
                ]
            );
        }

        // Doctor 2 Schedule - Available Tuesday to Saturday
        $days2 = [
            ['day' => 2, 'start' => '10:00', 'end' => '18:00'], // Tuesday
            ['day' => 3, 'start' => '10:00', 'end' => '18:00'], // Wednesday
            ['day' => 4, 'start' => '10:00', 'end' => '18:00'], // Thursday
            ['day' => 5, 'start' => '10:00', 'end' => '18:00'], // Friday
            ['day' => 6, 'start' => '09:00', 'end' => '14:00'], // Saturday
        ];

        foreach ($days2 as $day) {
            ProviderSchedule::firstOrCreate(
                [
                    'provider_profile_id' => $providers['doctor2']->id,
                    'day_of_week' => $day['day'],
                ],
                [
                    'start_time' => $day['start'],
                    'end_time' => $day['end'],
                    'is_available' => true,
                ]
            );
        }

        // Doctor 3 Schedule - Available Monday, Wednesday, Friday
        $days3 = [
            ['day' => 1, 'start' => '08:00', 'end' => '16:00'], // Monday
            ['day' => 3, 'start' => '08:00', 'end' => '16:00'], // Wednesday
            ['day' => 5, 'start' => '08:00', 'end' => '16:00'], // Friday
        ];

        foreach ($days3 as $day) {
            ProviderSchedule::firstOrCreate(
                [
                    'provider_profile_id' => $providers['doctor3']->id,
                    'day_of_week' => $day['day'],
                ],
                [
                    'start_time' => $day['start'],
                    'end_time' => $day['end'],
                    'is_available' => true,
                ]
            );
        }
    }

    /**
     * Create test appointments with various statuses
     */
    private function createTestAppointments(array $users, array $providers): void
    {
        // Pending appointment - Partner booking for child
        Appointment::firstOrCreate(
            [
                'user_id' => $users['partner1']->id,
                'provider_profile_id' => $providers['doctor1']->id,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00:00',
            ],
            [
                'end_time' => '10:45:00',
                'status' => 'pending',
                'notes' => 'Child: Emma (7 years) - Consultation for heart palpitations and chest discomfort during exercise.',
            ]
        );

        // Confirmed appointment - Partner booking for another child
        Appointment::firstOrCreate(
            [
                'user_id' => $users['partner1']->id,
                'provider_profile_id' => $providers['doctor2']->id,
                'appointment_date' => now()->addDays(5)->toDateString(),
                'start_time' => '14:00:00',
            ],
            [
                'end_time' => '14:30:00',
                'status' => 'confirmed',
                'notes' => 'Child: Lucas (10 years) - Follow-up for skin rash and acne treatment.',
            ]
        );

        // Completed appointment
        Appointment::firstOrCreate(
            [
                'user_id' => $users['partner2']->id,
                'provider_profile_id' => $providers['doctor1']->id,
                'appointment_date' => now()->subDays(7)->toDateString(),
                'start_time' => '11:00:00',
            ],
            [
                'end_time' => '11:45:00',
                'status' => 'completed',
                'notes' => 'Child: Sophie (5 years) - Annual cardiac checkup - child is healthy and developing well.',
            ]
        );

        // Cancelled appointment
        Appointment::firstOrCreate(
            [
                'user_id' => $users['partner2']->id,
                'provider_profile_id' => $providers['doctor3']->id,
                'appointment_date' => now()->subDays(2)->toDateString(),
                'start_time' => '09:00:00',
            ],
            [
                'end_time' => '09:30:00',
                'status' => 'cancelled',
                'notes' => 'Child: Oliver (3 years) - Cancelled due to child feeling better. Will reschedule if needed.',
            ]
        );

        // Multiple appointments for testing list/management
        Appointment::firstOrCreate(
            [
                'user_id' => $users['partner1']->id,
                'provider_profile_id' => $providers['doctor3']->id,
                'appointment_date' => now()->addDays(10)->toDateString(),
                'start_time' => '15:00:00',
            ],
            [
                'end_time' => '15:30:00',
                'status' => 'pending',
                'notes' => 'Child: Emma (7 years) - Routine vaccination and health checkup.',
            ]
        );
    }

    /**
     * Display test users for easy reference
     */
    private function displayTestUsers(): void
    {
        $this->command->info('📋 Test Users Created:');
        $this->command->newLine();
        
        $this->command->info('🔑 LOGIN CREDENTIALS (password: password for all)');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $this->command->info('👑 SUPER ADMIN (Full Access):');
        $this->command->line('   Email: superadmin@test.com');
        $this->command->line('   Can: Manage everything, view all, book, provide services');
        $this->command->newLine();
        
        $this->command->info('⚙️  ADMIN (System Manager):');
        $this->command->line('   Email: admin@test.com');
        $this->command->line('   Can: Add doctors, manage all bookings, view all appointments');
        $this->command->newLine();
        
        $this->command->info('👨‍⚕️ DOCTORS (Medical Providers):');
        $this->command->line('   Email: dr.smith@test.com (Cardiologist, 15 years exp)');
        $this->command->line('   Email: dr.johnson@test.com (Dermatologist, 10 years exp)');
        $this->command->line('   Email: dr.williams@test.com (Pediatrician, 8 years exp)');
        $this->command->line('   Can: Configure schedule/availability, chat with partners, view appointments');
        $this->command->newLine();
        
        $this->command->info('👨‍👩‍👧‍👦 PARTNERS (Parents/Guardians):');
        $this->command->line('   Email: partner1@test.com (Alice Partner)');
        $this->command->line('   Email: partner2@test.com (Bob Partner)');
        $this->command->line('   Can: Book appointments for children, add child info/problems, chat with doctors');
        $this->command->newLine();
        
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        
        $this->command->info('📊 TEST DATA SUMMARY:');
        $this->command->line('   ✓ 5 Specializations created');
        $this->command->line('   ✓ 7 Users with different roles');
        $this->command->line('   ✓ 3 Doctor profiles with schedules');
        $this->command->line('   ✓ 5 Test appointments (various statuses)');
        $this->command->newLine();
        
        $this->command->info('🧪 TEST CASES TO VERIFY:');
        $this->command->line('   1. Super Admin & Admin - Add new doctors with name and email');
        $this->command->line('   2. Doctors - Login and configure work schedule/availability');
        $this->command->line('   3. Doctors - Chat with partners and admin');
        $this->command->line('   4. Partners - Browse available doctors and book appointments');
        $this->command->line('   5. Partners - Add children info with problems/notes for each appointment');
        $this->command->line('   6. Partners - Chat with doctors about their children');
        $this->command->line('   7. View appointment statuses (pending/confirmed/completed/cancelled)');
        $this->command->line('   8. Test doctor availability slots based on their schedule');
    }
}
