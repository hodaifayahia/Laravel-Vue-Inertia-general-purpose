#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\City;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Bonoura Doctors by City:\n\n";

$city2926 = City::find(2926);
$city3035 = City::find(3035);

echo "City 2926 (بونورة - Bounoura, Province 47):\n";
$count1 = ProviderProfile::where('city_id', 2926)->count();
echo "  Doctors: {$count1}\n\n";

echo "City 3035 (بونورة - Bonoura, Province 35):\n";
$count2 = ProviderProfile::where('city_id', 3035)->count();
echo "  Doctors: {$count2}\n\n";

// Update all city 3035 doctors to city 2926
echo "Fixing: Moving doctors from city 3035 to city 2926...\n";
ProviderProfile::where('city_id', 3035)->update(['city_id' => 2926]);

echo "✅ Updated!\n\n";

// Verify
echo "Verification:\n";
$newCount1 = ProviderProfile::where('city_id', 2926)->count();
$newCount2 = ProviderProfile::where('city_id', 3035)->count();

echo "City 2926: {$newCount1} doctors\n";
echo "City 3035: {$newCount2} doctors\n";
