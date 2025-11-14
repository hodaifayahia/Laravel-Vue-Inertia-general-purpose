#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use Carbon\Carbon;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$provider = ProviderProfile::where('specialization_id', 6)->first();

echo "Provider: Dr. {$provider->user->name}\n";
echo "Slot Duration: {$provider->slot_duration} minutes\n\n";

echo "Work Schedule:\n";
$schedules = $provider->schedules()->orderBy('day_of_week')->get();
foreach ($schedules as $schedule) {
    $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $dayName = $dayNames[$schedule->day_of_week];
    echo "  {$dayName}: {$schedule->start_time} - {$schedule->end_time}\n";
}

echo "\nFinding next working day...\n";
$date = Carbon::now()->addDay();
for ($i = 0; $i < 7; $i++) {
    $dayOfWeek = $date->dayOfWeek;
    $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $dateStr = $date->format('Y-m-d');
    
    $schedule = $provider->schedules()->where('day_of_week', $dayOfWeek)->first();
    if ($schedule) {
        echo "✓ {$dateStr} ({$dayNames[$dayOfWeek]}): Working {$schedule->start_time} - {$schedule->end_time}\n";
        
        // Check slots
        $slots = $provider->getTimeSlotsForDate($dateStr);
        $available = collect($slots)->where('is_available', true)->count();
        echo "  Available slots: {$available}\n";
        
        if ($available > 0) {
            break;
        }
    } else {
        echo "✗ {$dateStr} ({$dayNames[$dayOfWeek]}): Not working\n";
    }
    
    $date->addDay();
}
