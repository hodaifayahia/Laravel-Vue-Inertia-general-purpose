#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\ProviderProfile;
use App\Models\User;
use Carbon\Carbon;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get or create a test patient
$patient = User::where('email', 'patient@example.com')->first() ?? User::factory()->create([
    'email' => 'patient@example.com',
    'name' => 'Test Patient',
]);

// Get a provider
$provider = ProviderProfile::where('specialization_id', 6)->first();

if (!$provider) {
    echo "No Dysgraphia specialists found!\n";
    exit(1);
}

echo "=== APPOINTMENT TEST ===\n\n";
echo "Patient: {$patient->name} ({$patient->email})\n";
echo "Provider: Dr. {$provider->user->name}\n\n";

// Find an available slot
$date = Carbon::now()->addDay();
for ($i = 0; $i < 7; $i++) {
    $dateString = $date->format('Y-m-d');
    $slots = $provider->getTimeSlotsForDate($dateString);
    $availableSlot = collect($slots)->firstWhere('is_available', true);
    
    if ($availableSlot) {
        break;
    }
    
    $date->addDay();
}

if (!$availableSlot) {
    echo "⚠️ No available slots found!\n";
    exit(1);
}

$dateString = $date->format('Y-m-d');

echo "Available slot: {$availableSlot['start_time']} - {$availableSlot['end_time']}\n\n";

// Create appointment
$appointment = Appointment::create([
    'user_id' => $patient->id,
    'provider_profile_id' => $provider->id,
    'appointment_date' => $dateString,
    'start_time' => $availableSlot['start_time'],
    'end_time' => $availableSlot['end_time'],
    'status' => 'pending',
    'notes' => 'Test appointment',
]);

echo "✅ Appointment created:\n";
echo "   ID: {$appointment->id}\n";
echo "   Status: {$appointment->status}\n";
echo "   Date: {$appointment->appointment_date}\n";
echo "   Time: {$appointment->start_time} - {$appointment->end_time}\n\n";

// Now check if the same slot is still available
echo "Checking slot availability AFTER booking...\n";
$slotsAfter = $provider->getTimeSlotsForDate($dateString);
$bookedSlot = collect($slotsAfter)->firstWhere('start_time', $availableSlot['start_time']);

if ($bookedSlot) {
    echo "Slot status after booking:\n";
    echo "  Start: {$bookedSlot['start_time']}\n";
    echo "  End: {$bookedSlot['end_time']}\n";
    echo "  Is Available: " . ($bookedSlot['is_available'] ? 'YES ❌ (WRONG!)' : 'NO ✅ (CORRECT!)') . "\n";
} else {
    echo "Slot not found!\n";
}

echo "\nTotal available slots before: " . collect($slots)->where('is_available', true)->count() . "\n";
echo "Total available slots after: " . collect($slotsAfter)->where('is_available', true)->count() . "\n";

// Cleanup
$appointment->delete();
echo "\n✅ Test appointment cleaned up.\n";
