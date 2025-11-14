#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\City;
use App\Models\Specialization;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== BOOKING SYSTEM VERIFICATION ===\n\n";

// Get Dysgraphia specialization
$dysgraphia = Specialization::where('name', 'Dysgraphia')->first();
if (!$dysgraphia) {
    echo "❌ Dysgraphia specialization not found!\n";
    exit(1);
}

echo "Dysgraphia ID: {$dysgraphia->id}\n\n";

// Test Ghardaia (city_id 2931)
$ghardaia = City::find(2931);
echo "GHARDAIA (City ID: 2931):\n";
echo "  Province: {$ghardaia->province_id}\n";

$ghardaiaProviders = ProviderProfile::where('city_id', 2931)
    ->with('user')
    ->where('specialization_id', $dysgraphia->id)
    ->get();

echo "  Dysgraphia Specialists: {$ghardaiaProviders->count()}\n";
if ($ghardaiaProviders->count() > 0) {
    foreach ($ghardaiaProviders as $provider) {
        echo "    ✓ {$provider->user->name} ({$provider->experience} years, {$provider->consultation_fee} DZD)\n";
    }
}

// Test Bonoura (city_id 2926)
$bonoura = City::find(2926);
echo "\nBONOURA (City ID: 2926):\n";
echo "  Province: {$bonoura->province_id}\n";

$bonouraProviders = ProviderProfile::where('city_id', 2926)
    ->with('user')
    ->where('specialization_id', $dysgraphia->id)
    ->get();

echo "  Dysgraphia Specialists: {$bonouraProviders->count()}\n";
if ($bonouraProviders->count() > 0) {
    foreach ($bonouraProviders as $provider) {
        echo "    ✓ {$provider->user->name} ({$provider->experience} years, {$provider->consultation_fee} DZD)\n";
    }
}

echo "\n=== TOTAL DYSGRAPHIA SPECIALISTS ===\n";
$totalDysgraphia = ProviderProfile::where('specialization_id', $dysgraphia->id)->count();

echo "Total: {$totalDysgraphia} specialists\n";
echo "  Ghardaia: {$ghardaiaProviders->count()}\n";
echo "  Bonoura: {$bonouraProviders->count()}\n";
echo "  Other: " . ($totalDysgraphia - $ghardaiaProviders->count() - $bonouraProviders->count()) . "\n";

if ($totalDysgraphia === 23) {
    echo "\n✅ BOOKING SYSTEM READY! All 23 specialists verified.\n";
} else {
    echo "\n⚠️ Expected 23 specialists, found {$totalDysgraphia}\n";
}
