#!/usr/bin/env php
<?php
/**
 * Quick Test Script for Child ID Booking
 * Run: php run_tests.php
 */

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Child;
use App\Models\Appointment;
use App\Models\ProviderProfile;
use App\Models\Specialization;
use App\Models\City;

echo "\n" . str_repeat("=", 60) . "\n";
echo "CHILD ID BOOKING SYSTEM - DIAGNOSTIC TESTS\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Check if child_id column exists
echo "TEST 1: Checking if child_id column exists...\n";
if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'child_id')) {
    echo "✓ PASS: child_id column exists\n\n";
} else {
    echo "✗ FAIL: child_id column NOT FOUND\n\n";
    exit(1);
}

// Test 2: Check Appointment model fillable
echo "TEST 2: Checking Appointment model fillable array...\n";
$appointmentModel = new Appointment();
if (in_array('child_id', $appointmentModel->getFillable())) {
    echo "✓ PASS: child_id in fillable array\n\n";
} else {
    echo "✗ FAIL: child_id NOT in fillable array\n\n";
    exit(1);
}

// Test 3: Create test data
echo "TEST 3: Setting up test data...\n";
try {
    // Create or get user
    $user = User::factory()->create();
    $user->givePermissionTo('can-book');
    
    // Create child
    $child = Child::factory()->create([
        'partner_id' => $user->id,
        'name' => 'Test Child - Ahmed',
    ]);
    
    // Create provider
    $provider = User::factory()->create();
    $city = City::factory()->create();
    $specialization = Specialization::factory()->create();
    $providerProfile = ProviderProfile::factory()->create([
        'user_id' => $provider->id,
        'city_id' => $city->id,
        'specialization_id' => $specialization->id,
        'is_available' => true,
    ]);
    
    echo "✓ PASS: Test data created\n";
    echo "  - User ID: {$user->id}\n";
    echo "  - Child ID: {$child->id} ({$child->name})\n";
    echo "  - Provider Profile ID: {$providerProfile->id}\n\n";
} catch (\Exception $e) {
    echo "✗ FAIL: Could not create test data\n";
    echo "  Error: {$e->getMessage()}\n\n";
    exit(1);
}

// Test 4: Create appointment with child_id
echo "TEST 4: Creating appointment with child_id...\n";
try {
    $appointmentData = [
        'provider_profile_id' => $providerProfile->id,
        'user_id' => $user->id,
        'child_id' => $child->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'pending',
        'notes' => 'Test appointment with child',
    ];
    
    $appointment = Appointment::create($appointmentData);
    echo "✓ PASS: Appointment created\n";
    echo "  - Appointment ID: {$appointment->id}\n";
    echo "  - child_id value: {$appointment->child_id}\n";
} catch (\Exception $e) {
    echo "✗ FAIL: Could not create appointment\n";
    echo "  Error: {$e->getMessage()}\n\n";
    exit(1);
}

// Test 5: Verify child_id is not NULL
echo "\nTEST 5: Verifying child_id was saved (not NULL)...\n";
if ($appointment->child_id === $child->id) {
    echo "✓ PASS: child_id correctly saved\n";
    echo "  - Expected: {$child->id}\n";
    echo "  - Actual: {$appointment->child_id}\n";
} else {
    echo "✗ FAIL: child_id not saved correctly\n";
    echo "  - Expected: {$child->id}\n";
    echo "  - Actual: {$appointment->child_id}\n\n";
    exit(1);
}

// Test 6: Verify child relationship
echo "\nTEST 6: Verifying child relationship...\n";
$refreshedAppointment = Appointment::with('child')->find($appointment->id);
if ($refreshedAppointment->child && $refreshedAppointment->child->id === $child->id) {
    echo "✓ PASS: Child relationship works\n";
    echo "  - Child Name: {$refreshedAppointment->child->name}\n";
    echo "  - Child ID: {$refreshedAppointment->child->id}\n";
} else {
    echo "✗ FAIL: Child relationship broken\n\n";
    exit(1);
}

// Test 7: Create appointment WITHOUT child
echo "\nTEST 7: Creating appointment WITHOUT child (null)...\n";
try {
    $appointmentNoChild = Appointment::create([
        'provider_profile_id' => $providerProfile->id,
        'user_id' => $user->id,
        'child_id' => null,
        'appointment_date' => now()->addDays(2)->toDateString(),
        'start_time' => '14:00',
        'end_time' => '15:00',
        'status' => 'pending',
    ]);
    
    if ($appointmentNoChild->child_id === null) {
        echo "✓ PASS: Appointment without child created successfully\n";
        echo "  - child_id is NULL: {$appointmentNoChild->child_id}\n";
    } else {
        echo "✗ FAIL: child_id should be NULL but is: {$appointmentNoChild->child_id}\n\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "✗ FAIL: Could not create appointment without child\n";
    echo "  Error: {$e->getMessage()}\n\n";
    exit(1);
}

// Test 8: Query appointments with child
echo "\nTEST 8: Querying appointments with child...\n";
$appointmentsWithChild = Appointment::whereNotNull('child_id')->with('child')->get();
if ($appointmentsWithChild->count() > 0) {
    echo "✓ PASS: Found {$appointmentsWithChild->count()} appointment(s) with child\n";
    foreach ($appointmentsWithChild as $apt) {
        echo "  - Appointment {$apt->id}: {$apt->child->name}\n";
    }
} else {
    echo "⚠ WARNING: No appointments with child found\n";
}

// Cleanup
echo "\nTEST 9: Cleaning up test data...\n";
try {
    $appointment->delete();
    $appointmentNoChild->delete();
    $child->delete();
    $user->delete();
    $providerProfile->delete();
    $provider->delete();
    $city->delete();
    $specialization->delete();
    echo "✓ PASS: Test data cleaned up\n\n";
} catch (\Exception $e) {
    echo "✗ FAIL: Could not clean up test data\n";
    echo "  Error: {$e->getMessage()}\n\n";
}

// Final summary
echo str_repeat("=", 60) . "\n";
echo "ALL TESTS PASSED! ✓\n";
echo "Child ID booking system is working correctly.\n";
echo str_repeat("=", 60) . "\n\n";
