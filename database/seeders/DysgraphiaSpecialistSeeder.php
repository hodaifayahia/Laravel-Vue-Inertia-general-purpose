<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Specialization;
use App\Models\ProviderProfile;
use App\Models\ProviderSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DysgraphiaSpecialistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Dysgraphia Specialist Seeder...');

        // Create or get the Dysgraphia specialization
        $this->command->info('📋 Creating/Getting Dysgraphia specialization...');
        $specialization = Specialization::firstOrCreate(
            ['slug' => 'dysgraphia'],
            [
                'name' => 'Dysgraphia',
                'description' => 'Specialists in diagnosing and treating dysgraphia (writing disorder)',
                'icon' => '✍️',
                'is_active' => true,
            ]
        );

        // Get or create provinces and cities for testing
        $this->command->info('📍 Setting up locations...');
        $locations = $this->setupLocations();

        // Create Dysgraphia specialists
        $this->command->info('👨‍⚕️ Creating Dysgraphia specialist profiles...');
        $specialists = $this->createDysgraphiaSpecialists($specialization, $locations);

        // Create schedules for each specialist
        $this->command->info('📅 Creating specialist schedules...');
        $this->createSpecialistSchedules($specialists);

        $this->command->info('✅ Dysgraphia Specialist Seeder completed successfully!');
        $this->command->newLine();
        $this->displaySpecialistInfo($specialists);
    }

    /**
     * Setup locations (provinces and cities)
     */
    private function setupLocations(): array
    {
        $locations = [];

        // Get or create Algiers (Wilaya 16)
        $algiersProvince = \App\Models\Province::firstOrCreate(
            ['code' => '16'],
            [
                'name_en' => 'Algiers',
                'name_ar' => 'الجزائر',
            ]
        );

        $algiersCity = \App\Models\City::firstOrCreate(
            ['name_en' => 'Algiers'],
            [
                'name_ar' => 'الجزائر',
                'province_id' => $algiersProvince->id,
            ]
        );

        $locations['algiers'] = [
            'province' => $algiersProvince,
            'city' => $algiersCity,
        ];

        // Get or create Oran (Wilaya 31)
        $oranProvince = \App\Models\Province::firstOrCreate(
            ['code' => '31'],
            [
                'name_en' => 'Oran',
                'name_ar' => 'وهران',
            ]
        );

        $oranCity = \App\Models\City::firstOrCreate(
            ['name_en' => 'Oran'],
            [
                'name_ar' => 'وهران',
                'province_id' => $oranProvince->id,
            ]
        );

        $locations['oran'] = [
            'province' => $oranProvince,
            'city' => $oranCity,
        ];

        // Get or create Constantine (Wilaya 25)
        $constantinProvince = \App\Models\Province::firstOrCreate(
            ['code' => '25'],
            [
                'name_en' => 'Constantine',
                'name_ar' => 'قسنطينة',
            ]
        );

        $constantinCity = \App\Models\City::firstOrCreate(
            ['name_en' => 'Constantine'],
            [
                'name_ar' => 'قسنطينة',
                'province_id' => $constantinProvince->id,
            ]
        );

        $locations['constantine'] = [
            'province' => $constantinProvince,
            'city' => $constantinCity,
        ];

        return $locations;
    }

    /**
     * Create Dysgraphia specialist profiles
     */
    private function createDysgraphiaSpecialists($specialization, $locations): array
    {
        $specialists = [];

        // Specialist 1 - In Algiers
        $user1 = User::firstOrCreate(
            ['email' => 'dysgraphia.dr1@test.com'],
            [
                'name' => 'Dr. Fatima Al-Zahra Benouira',
                'password' => Hash::make('password'),
            ]
        );
        $user1->assignRole('doctor');

        $specialists[] = ProviderProfile::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'specialization_id' => $specialization->id,
                'province_id' => $locations['algiers']['province']->id,
                'city_id' => $locations['algiers']['city']->id,
                'bio' => 'Specialized dysgraphia specialist with 12 years of clinical experience. Expert in diagnosing and treating writing disorders in children and adults. Certified in learning disability assessment and intervention.',
                'years_experience' => 12,
                'slot_duration' => 50,
                'is_available' => true,
                'title' => 'Dr.',
                'license_number' => 'DYSG-ALG-2024-001',
                'qualifications' => json_encode(['MD', 'Specialization in Learning Disorders', 'Educational Psychology Certification', 'Dysgraphia Assessment Specialist']),
                'languages' => json_encode(['Arabic', 'French', 'English']),
                'phone' => '+213-555-1001',
                'office_address' => '123 Learning Disorder Center, Algiers Medical District',
                'clinic_name' => 'Algiers Dysgraphia & Learning Center',
                'consultation_fee' => 180.00,
                'advance_booking_days' => 30,
                'services_offered' => json_encode(['Dysgraphia Assessment', 'Writing Disorder Therapy', 'Occupational Therapy', 'Educational Counseling', 'Progress Monitoring']),
                'education' => json_encode([
                    ['degree' => 'MD in Pediatrics', 'institution' => 'Algiers Medical University', 'year' => '2012'],
                    ['degree' => 'Specialization in Learning Disorders', 'institution' => 'University of Paris VII', 'year' => '2015'],
                    ['degree' => 'Dysgraphia & Writing Disorders Certification', 'institution' => 'International Learning Disability Institute', 'year' => '2018']
                ]),
                'awards' => json_encode([
                    ['title' => 'Best Dysgraphia Specialist Award', 'year' => '2022'],
                    ['title' => 'Excellence in Learning Disorder Treatment', 'year' => '2020'],
                    ['title' => 'Most Trusted Specialist', 'year' => '2023']
                ]),
                'website' => 'https://algierslearningcenter.com',
                'social_links' => json_encode([
                    'facebook' => 'https://facebook.com/algierslearningcenter',
                    'linkedin' => 'https://linkedin.com/in/dr-fatima-benouira'
                ]),
                'rating' => 4.8,
                'total_reviews' => 45,
                'total_patients' => 320,
            ]
        );

        // Specialist 2 - In Oran
        $user2 = User::firstOrCreate(
            ['email' => 'dysgraphia.dr2@test.com'],
            [
                'name' => 'Prof. Ahmed Boukhelkhal',
                'password' => Hash::make('password'),
            ]
        );
        $user2->assignRole('doctor');

        $specialists[] = ProviderProfile::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'specialization_id' => $specialization->id,
                'province_id' => $locations['oran']['province']->id,
                'city_id' => $locations['oran']['city']->id,
                'bio' => 'Professor and leading dysgraphia researcher with 18 years of experience. Published numerous studies on writing disorders. Highly experienced in complex cases and rehabilitation programs.',
                'years_experience' => 18,
                'slot_duration' => 60,
                'is_available' => true,
                'title' => 'Prof.',
                'license_number' => 'DYSG-ORN-2024-002',
                'qualifications' => json_encode(['MD', 'PhD in Education', 'Specialization in Dysgraphia', 'Research in Writing Disorders', 'Clinical Psychologist']),
                'languages' => json_encode(['Arabic', 'French', 'English', 'Spanish']),
                'phone' => '+213-555-1002',
                'office_address' => '456 Oran Dysgraphia Institute, Oran Educational District',
                'clinic_name' => 'Oran Institute for Writing Disorders',
                'consultation_fee' => 220.00,
                'advance_booking_days' => 45,
                'services_offered' => json_encode(['Advanced Dysgraphia Assessment', 'Intensive Therapy Programs', 'Research-Based Interventions', 'Family Counseling', 'Teacher Training']),
                'education' => json_encode([
                    ['degree' => 'MD', 'institution' => 'Oran University Medical School', 'year' => '2005'],
                    ['degree' => 'PhD in Education & Learning Disorders', 'institution' => 'University of Lyon', 'year' => '2010'],
                    ['degree' => 'Postdoctoral Research in Dysgraphia', 'institution' => 'International Research Center', 'year' => '2012']
                ]),
                'awards' => json_encode([
                    ['title' => 'Research Excellence Award', 'year' => '2021'],
                    ['title' => 'Distinguished Professor Award', 'year' => '2020'],
                    ['title' => 'Lifetime Achievement in Learning Disability Treatment', 'year' => '2023']
                ]),
                'website' => 'https://orindysgraphiainstitute.com',
                'social_links' => json_encode([
                    'facebook' => 'https://facebook.com/orandysgraphiainstitute',
                    'linkedin' => 'https://linkedin.com/in/prof-ahmed-boukhelkhal',
                    'researchgate' => 'https://researchgate.net/ahmed-boukhelkhal'
                ]),
                'rating' => 4.9,
                'total_reviews' => 78,
                'total_patients' => 580,
            ]
        );

        // Specialist 3 - In Constantine
        $user3 = User::firstOrCreate(
            ['email' => 'dysgraphia.dr3@test.com'],
            [
                'name' => 'Dr. Leila Mansouri',
                'password' => Hash::make('password'),
            ]
        );
        $user3->assignRole('doctor');

        $specialists[] = ProviderProfile::updateOrCreate(
            ['user_id' => $user3->id],
            [
                'specialization_id' => $specialization->id,
                'province_id' => $locations['constantine']['province']->id,
                'city_id' => $locations['constantine']['city']->id,
                'bio' => 'Compassionate dysgraphia specialist with 10 years of hands-on clinical experience. Specializes in child and adolescent writing disorders. Known for creating personalized treatment plans and building strong rapport with patients.',
                'years_experience' => 10,
                'slot_duration' => 45,
                'is_available' => true,
                'title' => 'Dr.',
                'license_number' => 'DYSG-CST-2024-003',
                'qualifications' => json_encode(['MD', 'Specialization in Dysgraphia', 'Occupational Therapy Certification', 'Child Psychology Specialist']),
                'languages' => json_encode(['Arabic', 'French', 'English']),
                'phone' => '+213-555-1003',
                'office_address' => '789 Constantine Learning Center, Educational Complex',
                'clinic_name' => 'Constantine Dysgraphia Clinic',
                'consultation_fee' => 160.00,
                'advance_booking_days' => 28,
                'services_offered' => json_encode(['Dysgraphia Diagnosis', 'Personalized Therapy', 'Handwriting Rehabilitation', 'Motor Skill Development', 'School Coordination']),
                'education' => json_encode([
                    ['degree' => 'MD in Pediatrics', 'institution' => 'Constantine Medical University', 'year' => '2014'],
                    ['degree' => 'Specialization in Dysgraphia', 'institution' => 'Brussels Learning Disorder Center', 'year' => '2017'],
                    ['degree' => 'Occupational Therapy Certification', 'institution' => 'International OT Institute', 'year' => '2019']
                ]),
                'awards' => json_encode([
                    ['title' => 'Compassionate Care Award', 'year' => '2021'],
                    ['title' => 'Best Patient Outcomes', 'year' => '2022']
                ]),
                'website' => 'https://constantinedysgraphia.com',
                'social_links' => json_encode([
                    'facebook' => 'https://facebook.com/constantinedysgraphiaclinic',
                    'linkedin' => 'https://linkedin.com/in/dr-leila-mansouri'
                ]),
                'rating' => 4.7,
                'total_reviews' => 32,
                'total_patients' => 240,
            ]
        );

        return $specialists;
    }

    /**
     * Create schedules for specialists
     */
    private function createSpecialistSchedules($specialists): void
    {
        // All specialists work Monday to Friday
        $workDays = [
            ['day' => 1, 'start' => '09:00', 'end' => '17:00'], // Monday
            ['day' => 2, 'start' => '09:00', 'end' => '17:00'], // Tuesday
            ['day' => 3, 'start' => '09:00', 'end' => '17:00'], // Wednesday
            ['day' => 4, 'start' => '10:00', 'end' => '18:00'], // Thursday
            ['day' => 5, 'start' => '09:00', 'end' => '17:00'], // Friday
        ];

        foreach ($specialists as $specialist) {
            foreach ($workDays as $day) {
                ProviderSchedule::firstOrCreate(
                    [
                        'provider_profile_id' => $specialist->id,
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
    }

    /**
     * Display specialist information
     */
    private function displaySpecialistInfo($specialists): void
    {
        $this->command->info('✨ Dysgraphia Specialists Created Successfully!');
        $this->command->newLine();
        
        $this->command->info('🔑 LOGIN CREDENTIALS (password: password for all)');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        foreach ($specialists as $index => $specialist) {
            $user = $specialist->user;
            $location = $specialist->city->name_ar . ' - ' . $specialist->province->name_ar;
            
            $this->command->info(($index + 1) . '. ' . $user->name);
            $this->command->line('   Email: ' . $user->email);
            $this->command->line('   Location: ' . $location);
            $this->command->line('   Experience: ' . $specialist->years_experience . ' years');
            $this->command->line('   Consultation Fee: ' . $specialist->consultation_fee . ' DZD');
            $this->command->line('   Rating: ' . $specialist->rating . '/5 (' . $specialist->total_reviews . ' reviews)');
            $this->command->line('   Total Patients: ' . $specialist->total_patients);
            $this->command->newLine();
        }
        
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        
        $this->command->info('✅ READY TO TEST:');
        $this->command->line('   • All specialists have confirmed Dysgraphia specialization');
        $this->command->line('   • Complete availability schedules (Mon-Fri)');
        $this->command->line('   • Located in different provinces for location filtering');
        $this->command->line('   • Ready to book appointments!');
    }
}
