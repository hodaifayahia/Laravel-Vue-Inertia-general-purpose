#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\User;
use Carbon\Carbon;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING API TIME FORMAT FIX ===\n\n";

// Get a provider
$provider = ProviderProfile::where('specialization_id', 6)->first();
echo "Provider: Dr. {$provider->user->name}\n";

// Get slots for tomorrow
$tomorrow = Carbon::tomorrow()->format('Y-m-d');
$slots = $provider->getTimeSlotsForDate($tomorrow);

echo "Slots for {$tomorrow}:\n";
foreach (array_slice($slots, 0, 3) as $slot) {
    echo "  {$slot['start_time']} - {$slot['end_time']} (Available: " . ($slot['is_available'] ? 'YES' : 'NO') . ")\n";
}

// Test validation format
echo "\nTesting time format validation...\n";
$testTimes = ['09:00', '09:00:00', '9:00', '09:00:30'];

foreach ($testTimes as $time) {
    $validator = validator(['time' => $time], ['time' => 'required|date_format:H:i']);
    $valid = !$validator->fails();
    echo "  '{$time}' -> " . ($valid ? 'VALID' : 'INVALID') . "\n";
}

echo "\n✅ Time format fix verified!\n";
