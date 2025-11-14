<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProviderProfile;
use App\Models\ProviderAvailability;
use Carbon\Carbon;

echo "Setting up test data for availability system...\n\n";

// Create provider user
$provider = User::firstOrCreate(
    ['email' => 'doctor@test.com'],
    [
        'name' => 'Dr. Test Provider',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);
$provider->assignRole('provider');
$provider->givePermissionTo('book-sys');

// Create patient user
$patient = User::firstOrCreate(
    ['email' => 'patient@test.com'],
    [
        'name' => 'Test Patient',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);
$patient->assignRole('patient');

// Create provider profile
$profile = ProviderProfile::updateOrCreate(
    ['user_id' => $provider->id],
    [
        'specialization' => 'General Medicine',
        'bio' => 'Experienced general practitioner with 10 years of practice.',
        'consultation_fee' => 100.00,
        'is_available' => true,
        'languages' => json_encode(['English', 'French']),
        'education' => 'MD from Medical University',
        'experience_years' => 10
    ]
);

// Create some test availability dates for the next 7 days
echo "Creating availability for next 7 days...\n";
for ($i = 1; $i <= 7; $i++) {
    $date = Carbon::now()->addDays($i)->format('Y-m-d');
    
    ProviderAvailability::updateOrCreate(
        [
            'provider_profile_id' => $profile->id,
            'date' => $date
        ],
        [
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_available' => true,
            'reason' => 'Regular working hours'
        ]
    );
    echo "  ✓ Added availability for $date (09:00 - 17:00)\n";
}

// Add one unavailable date
$unavailableDate = Carbon::now()->addDays(8)->format('Y-m-d');
ProviderAvailability::updateOrCreate(
    [
        'provider_profile_id' => $profile->id,
        'date' => $unavailableDate
    ],
    [
        'start_time' => null,
        'end_time' => null,
        'is_available' => false,
        'reason' => 'Day off'
    ]
);
echo "  ✓ Added unavailable date for $unavailableDate\n";

echo "\n✅ Test setup complete!\n\n";
echo "Login Credentials:\n";
echo "==================\n";
echo "Provider (Doctor):\n";
echo "  Email: doctor@test.com\n";
echo "  Password: password\n";
echo "  ID: {$provider->id}\n";
echo "\nPatient:\n";
echo "  Email: patient@test.com\n";
echo "  Password: password\n";
echo "  ID: {$patient->id}\n";
echo "\nProvider Profile ID: {$profile->id}\n";
echo "\n📅 Availability Summary:\n";
echo "  - Next 7 days: Available (09:00 - 17:00)\n";
echo "  - Day 8 ({$unavailableDate}): Unavailable (Day off)\n";
echo "\n🧪 Test Flow:\n";
echo "  1. Login as doctor@test.com\n";
echo "  2. Go to 'Manage Availability'\n";
echo "  3. View/Edit availability dates\n";
echo "  4. Logout and login as patient@test.com\n";
echo "  5. Go to bookings page\n";
echo "  6. Select the doctor\n";
echo "  7. View available dates and book appointment\n";
echo "\n";
