<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Specialization;
use App\Models\ProviderProfile;
use App\Models\ProviderSchedule;
use App\Models\Province;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdditionalDysgraphiaSpecialistsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Additional Dysgraphia Specialists Seeder...');

        // Get the Dysgraphia specialization
        $dysgraphia = Specialization::where('slug', 'dysgraphia')->first();
        if (!$dysgraphia) {
            $this->command->error('Dysgraphia specialization not found. Run DysgraphiaSpecialistSeeder first.');
            return;
        }

        // Setup or get Ghardaia and Bonoura
        $locations = $this->setupLocations();

        // Create 10 doctors for Ghardaia
        $this->command->info('👨‍⚕️ Creating 10 Dysgraphia doctors for Ghardaia...');
        $ghardaiaProviders = $this->createGhardaiaDoctors($dysgraphia, $locations['ghardaia']);

        // Create 10 doctors for Bonoura
        $this->command->info('👨‍⚕️ Creating 10 Dysgraphia doctors for Bonoura...');
        $bonouraProviders = $this->createBonouraDoctors($dysgraphia, $locations['bonoura']);

        // Create schedules for all new doctors
        $this->command->info('📅 Creating schedules for all new doctors...');
        $allProviders = array_merge($ghardaiaProviders, $bonouraProviders);
        $this->createSchedulesForProviders($allProviders);

        $this->command->info('✅ Additional Dysgraphia Specialists Seeder completed successfully!');
        $this->command->newLine();
        $this->displaySpecialistInfo($ghardaiaProviders, $bonouraProviders);
    }

    /**
     * Setup or get locations
     */
    private function setupLocations(): array
    {
        $locations = [];

        // Get or create Ghardaia (Wilaya 47)
        $ghardaiaProvince = Province::firstOrCreate(
            ['code' => '47'],
            [
                'name_en' => 'Ghardaia',
                'name_ar' => 'غرداية',
            ]
        );

        $ghardaiaCity = City::firstOrCreate(
            ['name_en' => 'Ghardaia'],
            [
                'name_ar' => 'غرداية',
                'province_id' => $ghardaiaProvince->id,
            ]
        );

        $locations['ghardaia'] = [
            'province' => $ghardaiaProvince,
            'city' => $ghardaiaCity,
        ];

        // Get or create Bonoura (Wilaya 35)
        $bonouraProvince = Province::firstOrCreate(
            ['code' => '35'],
            [
                'name_en' => 'Bonoura',
                'name_ar' => 'بونورة',
            ]
        );

        $bonourCity = City::firstOrCreate(
            ['name_en' => 'Bonoura'],
            [
                'name_ar' => 'بونورة',
                'province_id' => $bonouraProvince->id,
            ]
        );

        $locations['bonoura'] = [
            'province' => $bonouraProvince,
            'city' => $bonourCity,
        ];

        return $locations;
    }

    /**
     * Create 10 Dysgraphia doctors for Ghardaia
     */
    private function createGhardaiaDoctors($specialization, $location): array
    {
        $providers = [];
        $doctorNames = [
            'Dr. محمد علي الشرقاوي',
            'Dr. فاطمة حسن البراهيم',
            'Dr. أحمد محمود السعيد',
            'Dr. نور حسين العمري',
            'Dr. سارة إبراهيم الحاج',
            'Dr. عمر الطاهر الشامخ',
            'Dr. ليلى محمد القاسم',
            'Dr. خالد جابر الغانم',
            'Dr. هند عبدالله المحمدي',
            'Dr. ياسر إبراهيم الأمين',
        ];

        $emails = [
            'ghardaia.dr1@test.com',
            'ghardaia.dr2@test.com',
            'ghardaia.dr3@test.com',
            'ghardaia.dr4@test.com',
            'ghardaia.dr5@test.com',
            'ghardaia.dr6@test.com',
            'ghardaia.dr7@test.com',
            'ghardaia.dr8@test.com',
            'ghardaia.dr9@test.com',
            'ghardaia.dr10@test.com',
        ];

        $experiences = [8, 12, 6, 15, 9, 11, 7, 10, 14, 5];
        $fees = [150, 180, 140, 200, 160, 175, 145, 170, 190, 135];
        $ratings = [4.6, 4.8, 4.5, 4.9, 4.7, 4.8, 4.6, 4.7, 4.9, 4.4];
        $reviews = [28, 42, 22, 56, 35, 38, 30, 40, 48, 18];
        $patients = [210, 315, 165, 420, 260, 285, 225, 300, 360, 135];

        for ($i = 0; $i < 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => $emails[$i]],
                [
                    'name' => $doctorNames[$i],
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole('doctor');

            $providers[] = ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialization_id' => $specialization->id,
                    'province_id' => $location['province']->id,
                    'city_id' => $location['city']->id,
                    'bio' => "متخصص في اضطرابات الكتابة (الديسجرافيا) مع خبرة {$experiences[$i]} سنة. متفاني في تقديم رعاية شخصية لكل مريض.",
                    'years_experience' => $experiences[$i],
                    'slot_duration' => 45,
                    'is_available' => true,
                    'title' => 'Dr.',
                    'license_number' => 'DYSG-GHA-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'qualifications' => json_encode(['MD', 'Specialization in Dysgraphia', 'Writing Disorders Certification']),
                    'languages' => json_encode(['Arabic', 'French', 'English']),
                    'phone' => '+213-555-' . str_pad(5000 + $i, 4, '0', STR_PAD_LEFT),
                    'office_address' => 'عنوان العيادة ' . ($i + 1) . ', غرداية',
                    'clinic_name' => 'عيادة تصحيح الكتابة الغرداوية ' . ($i + 1),
                    'consultation_fee' => $fees[$i],
                    'advance_booking_days' => 30,
                    'services_offered' => json_encode(['تقييم الديسجرافيا', 'العلاج الكتابي', 'تطوير المهارات الحركية', 'استشارات تعليمية']),
                    'education' => json_encode([
                        ['degree' => 'MD in Pediatrics', 'institution' => 'Algerian Medical University', 'year' => date('Y') - $experiences[$i]],
                        ['degree' => 'Specialization in Dysgraphia', 'institution' => 'International Learning Institute', 'year' => date('Y') - ($experiences[$i] - 3)]
                    ]),
                    'awards' => json_encode([
                        ['title' => 'Best Treatment Award', 'year' => date('Y') - 1],
                    ]),
                    'website' => 'https://dysgraphia-ghardaia' . ($i + 1) . '.com',
                    'social_links' => json_encode([
                        'facebook' => 'https://facebook.com/ghardaiadysgraphia' . ($i + 1),
                        'linkedin' => 'https://linkedin.com/in/ghardaia-dr' . ($i + 1)
                    ]),
                    'rating' => $ratings[$i],
                    'total_reviews' => $reviews[$i],
                    'total_patients' => $patients[$i],
                ]
            );
        }

        return $providers;
    }

    /**
     * Create 10 Dysgraphia doctors for Bonoura
     */
    private function createBonouraDoctors($specialization, $location): array
    {
        $providers = [];
        $doctorNames = [
            'Dr. محمود عبدالرحمن',
            'Dr. زينب محمد الزهراء',
            'Dr. جمال الدين الحسن',
            'Dr. أمل إبراهيم الرحيم',
            'Dr. علي حسن الدعيع',
            'Dr. مريم أحمد القحطاني',
            'Dr. إبراهيم محمود الخليل',
            'Dr. عائشة علي الحوراني',
            'Dr. صالح جابر الجابري',
            'Dr. هاجر محمد الحربي',
        ];

        $emails = [
            'bonoura.dr1@test.com',
            'bonoura.dr2@test.com',
            'bonoura.dr3@test.com',
            'bonoura.dr4@test.com',
            'bonoura.dr5@test.com',
            'bonoura.dr6@test.com',
            'bonoura.dr7@test.com',
            'bonoura.dr8@test.com',
            'bonoura.dr9@test.com',
            'bonoura.dr10@test.com',
        ];

        $experiences = [13, 9, 7, 16, 10, 8, 12, 11, 6, 14];
        $fees = [170, 155, 145, 210, 165, 150, 180, 175, 140, 195];
        $ratings = [4.7, 4.6, 4.5, 4.9, 4.6, 4.7, 4.8, 4.7, 4.4, 4.8];
        $reviews = [35, 30, 20, 52, 32, 28, 44, 38, 16, 46];
        $patients = [260, 225, 150, 390, 240, 210, 330, 285, 120, 345];

        for ($i = 0; $i < 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => $emails[$i]],
                [
                    'name' => $doctorNames[$i],
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole('doctor');

            $providers[] = ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialization_id' => $specialization->id,
                    'province_id' => $location['province']->id,
                    'city_id' => $location['city']->id,
                    'bio' => "متخصص عالي الكفاءة في معالجة اضطرابات الكتابة (الديسجرافيا) مع خبرة {$experiences[$i]} سنة. ملتزم بتقديم أفضل الخدمات العلاجية.",
                    'years_experience' => $experiences[$i],
                    'slot_duration' => 50,
                    'is_available' => true,
                    'title' => 'Dr.',
                    'license_number' => 'DYSG-BON-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'qualifications' => json_encode(['MD', 'Specialization in Dysgraphia', 'Educational Psychology Certification']),
                    'languages' => json_encode(['Arabic', 'French', 'Tamazight', 'English']),
                    'phone' => '+213-555-' . str_pad(6000 + $i, 4, '0', STR_PAD_LEFT),
                    'office_address' => 'عنوان العيادة ' . ($i + 1) . ', بونورة',
                    'clinic_name' => 'مركز بونورة لعلاج الديسجرافيا ' . ($i + 1),
                    'consultation_fee' => $fees[$i],
                    'advance_booking_days' => 28,
                    'services_offered' => json_encode(['التقييم المتقدم', 'برامج علاجية متخصصة', 'تطوير مهارات الكتابة', 'استشارات عائلية']),
                    'education' => json_encode([
                        ['degree' => 'MD in Pediatrics', 'institution' => 'Algerian Medical University', 'year' => date('Y') - $experiences[$i]],
                        ['degree' => 'Specialization in Learning Disorders', 'institution' => 'International University', 'year' => date('Y') - ($experiences[$i] - 4)]
                    ]),
                    'awards' => json_encode([
                        ['title' => 'Excellence Award', 'year' => date('Y') - 1],
                        ['title' => 'Top Specialist', 'year' => date('Y') - 2],
                    ]),
                    'website' => 'https://dysgraphia-bonoura' . ($i + 1) . '.com',
                    'social_links' => json_encode([
                        'facebook' => 'https://facebook.com/bonouradysgraphia' . ($i + 1),
                        'linkedin' => 'https://linkedin.com/in/bonoura-dr' . ($i + 1)
                    ]),
                    'rating' => $ratings[$i],
                    'total_reviews' => $reviews[$i],
                    'total_patients' => $patients[$i],
                ]
            );
        }

        return $providers;
    }

    /**
     * Create schedules for all providers
     */
    private function createSchedulesForProviders($providers): void
    {
        // Standard work schedule: Mon-Fri with varying times
        $schedules = [
            ['day' => 1, 'start' => '09:00', 'end' => '17:00'], // Monday
            ['day' => 2, 'start' => '09:00', 'end' => '17:00'], // Tuesday
            ['day' => 3, 'start' => '09:00', 'end' => '17:00'], // Wednesday
            ['day' => 4, 'start' => '10:00', 'end' => '18:00'], // Thursday
            ['day' => 5, 'start' => '09:00', 'end' => '17:00'], // Friday
        ];

        foreach ($providers as $provider) {
            foreach ($schedules as $schedule) {
                ProviderSchedule::firstOrCreate(
                    [
                        'provider_profile_id' => $provider->id,
                        'day_of_week' => $schedule['day'],
                    ],
                    [
                        'start_time' => $schedule['start'],
                        'end_time' => $schedule['end'],
                        'is_available' => true,
                    ]
                );
            }
        }
    }

    /**
     * Display specialist information
     */
    private function displaySpecialistInfo($ghardaiaProviders, $bonouraProviders): void
    {
        $this->command->info('✨ Additional Dysgraphia Specialists Created Successfully!');
        $this->command->newLine();

        $this->command->info('🏥 GHARDAIA - 10 Dysgraphia Specialists');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        foreach ($ghardaiaProviders as $index => $specialist) {
            $user = $specialist->user;
            $this->command->line(($index + 1) . '. ' . $user->name);
            $this->command->line('   Email: ' . $user->email . ' | Fee: ' . $specialist->consultation_fee . ' DZD | Rating: ' . $specialist->rating . '/5');
        }
        $this->command->newLine();

        $this->command->info('🏥 BONOURA - 10 Dysgraphia Specialists');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        foreach ($bonouraProviders as $index => $specialist) {
            $user = $specialist->user;
            $this->command->line(($index + 1) . '. ' . $user->name);
            $this->command->line('   Email: ' . $user->email . ' | Fee: ' . $specialist->consultation_fee . ' DZD | Rating: ' . $specialist->rating . '/5');
        }
        $this->command->newLine();

        $this->command->info('📊 TOTAL STATISTICS:');
        $this->command->line('   ✓ Ghardaia: 10 specialists');
        $this->command->line('   ✓ Bonoura: 10 specialists');
        $this->command->line('   ✓ All with schedules (Mon-Fri)');
        $this->command->line('   ✓ All with complete profiles');
        $this->command->newLine();

        $this->command->info('🔑 Password for all doctors: password');
        $this->command->newLine();
    }
}
