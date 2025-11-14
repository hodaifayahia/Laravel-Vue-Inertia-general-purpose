<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProviderProfile;
use App\Models\ProviderAvailability;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

echo "Setting up test data for availability system...\n\n";

// Create roles if they don't exist
echo "Creating roles and permissions...\n";
$providerRole = Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);
$patientRole = Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
$bookPermission = Permission::firstOrCreate(['name' => 'book-sys', 'guard_name' => 'web']);
$providerRole->givePermissionTo($bookPermission);
echo "  ✓ Roles and permissions created\n\n";

// Create provider user
echo "Creating test users...\n";
$provider = User::where('email', 'doctor@test.com')->first();
if (!$provider) {
    $provider = User::create([
        'name' => 'Dr. Test Provider',
        'email' => 'doctor@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]);
    echo "  ✓ Created provider user\n";
} else {
    echo "  ✓ Provider user already exists\n";
}
$provider->syncRoles(['provider']);
$provider->givePermissionTo('book-sys');

// Create patient user
$patient = User::where('email', 'patient@test.com')->first();
if (!$patient) {
    $patient = User::create([
        'name' => 'Test Patient',
        'email' => 'patient@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]);
    echo "  ✓ Created patient user\n\n";
} else {
    echo "  ✓ Patient user already exists\n\n";
}
$patient->syncRoles(['patient']);

// Create provider profile
echo "Creating provider profile...\n";
$profile = ProviderProfile::updateOrCreate(
    ['user_id' => $provider->id],
    [
        'specialization_id' => 1, // Cardiology
        'bio' => 'Experienced general practitioner with 10 years of practice. Specializes in family medicine and preventive care.',
        'consultation_fee' => 100.00,
        'is_available' => true,
        'languages' => json_encode(['English', 'French']),
        'education' => 'MD from Medical University, Board Certified',
        'experience_years' => 10
    ]
);
echo "  ✓ Provider profile created/updated\n\n";

// Clear existing availability
ProviderAvailability::where('provider_profile_id', $profile->id)->delete();

// Create some test availability dates for the next 7 days
echo "Creating availability for next 7 days...\n";
for ($i = 1; $i <= 7; $i++) {
    $date = Carbon::now()->addDays($i)->format('Y-m-d');
    
    ProviderAvailability::create([
        'provider_profile_id' => $profile->id,
        'date' => $date,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_available' => true,
        'reason' => 'Regular working hours'
    ]);
    echo "  ✓ {$date} (09:00 - 17:00) - Available\n";
}

// Add one unavailable date
$unavailableDate = Carbon::now()->addDays(8)->format('Y-m-d');
ProviderAvailability::create([
    'provider_profile_id' => $profile->id,
    'date' => $unavailableDate,
    'start_time' => null,
    'end_time' => null,
    'is_available' => false,
    'reason' => 'Day off'
]);
echo "  ✓ {$unavailableDate} - Unavailable (Day off)\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ TEST SETUP COMPLETE!\n";
echo str_repeat("=", 60) . "\n\n";

echo "📋 LOGIN CREDENTIALS:\n";
echo str_repeat("-", 60) . "\n";
echo "Provider (Doctor):\n";
echo "  Email:    doctor@test.com\n";
echo "  Password: password\n";
echo "  User ID:  {$provider->id}\n";
echo "  Profile ID: {$profile->id}\n";
echo "\nPatient:\n";
echo "  Email:    patient@test.com\n";
echo "  Password: password\n";
echo "  User ID:  {$patient->id}\n";

echo "\n📅 AVAILABILITY DATA:\n";
echo str_repeat("-", 60) . "\n";
$availableCount = ProviderAvailability::where('provider_profile_id', $profile->id)
    ->where('is_available', true)->count();
$unavailableCount = ProviderAvailability::where('provider_profile_id', $profile->id)
    ->where('is_available', false)->count();
echo "  Available dates: {$availableCount}\n";
echo "  Unavailable dates: {$unavailableCount}\n";
echo "  Total records: " . ($availableCount + $unavailableCount) . "\n";

echo "\n🧪 TESTING STEPS:\n";
echo str_repeat("-", 60) . "\n";
echo "1. PROVIDER TESTING:\n";
echo "   a. Login as doctor@test.com\n";
echo "   b. Navigate to 'Manage Availability' from sidebar\n";
echo "   c. Verify calendar shows next 3 months\n";
echo "   d. Check available dates are marked in green\n";
echo "   e. Check unavailable date is marked in red\n";
echo "   f. Test selecting multiple dates\n";
echo "   g. Test 'Mark as Available' dialog\n";
echo "   h. Test 'Mark as Unavailable' dialog\n";
echo "   i. Test 'Bulk Set Availability' dialog\n";
echo "   j. Test removing availability\n";
echo "   k. Verify success messages appear\n";
echo "\n2. PATIENT TESTING:\n";
echo "   a. Logout and login as patient@test.com\n";
echo "   b. Navigate to bookings/create page\n";
echo "   c. Select Dr. Test Provider\n";
echo "   d. Verify available dates show in calendar\n";
echo "   e. Select an available date\n";
echo "   f. Choose a time slot (should be within 09:00-17:00)\n";
echo "   g. Fill in booking details\n";
echo "   h. Submit booking\n";
echo "   i. Verify booking is created\n";
echo "\n3. VALIDATION TESTING:\n";
echo "   a. Try booking unavailable date (should fail)\n";
echo "   b. Try booking past date (should fail)\n";
echo "   c. Try booking outside working hours (should fail)\n";
echo "\n" . str_repeat("=", 60) . "\n";
echo "✨ You can now test the system in your browser!\n";
echo str_repeat("=", 60) . "\n";
