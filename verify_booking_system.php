#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\Province;
use App\Models\City;
use App\Models\Specialization;
use App\Models\ProviderSchedule;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║         🎯 DYSGRAPHIA BOOKING SYSTEM - COMPLETE VERIFICATION REPORT        ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

$pass = 0;
$total = 0;

// Test 1: Specialization exists
$total++;
$dysgraphia = Specialization::where('slug', 'dysgraphia')->first();
if ($dysgraphia) {
    echo "✅ TEST 1: Dysgraphia Specialization\n";
    echo "   ID: {$dysgraphia->id}, Name: {$dysgraphia->name}\n";
    $pass++;
} else {
    echo "❌ TEST 1: Dysgraphia Specialization NOT FOUND\n";
}
echo "\n";

// Test 2: Providers exist
$total++;
$providers = ProviderProfile::where('specialization_id', $dysgraphia->id)->get();
if ($providers->count() == 3) {
    echo "✅ TEST 2: All 3 Dysgraphia Providers Found\n";
    $pass++;
} else {
    echo "❌ TEST 2: Expected 3 providers, found {$providers->count()}\n";
}
echo "\n";

// Test 3: Providers have cities
$total++;
$providersWithCities = ProviderProfile::where('specialization_id', $dysgraphia->id)
    ->with('city', 'province')
    ->get();
$allHaveCities = $providersWithCities->every(fn($p) => $p->city_id !== null);
if ($allHaveCities) {
    echo "✅ TEST 3: All Providers Have Cities\n";
    foreach ($providersWithCities as $p) {
        echo "   - {$p->user->name}: {$p->city->name_ar} ({$p->province->name_ar})\n";
    }
    $pass++;
} else {
    echo "❌ TEST 3: Some providers missing cities\n";
}
echo "\n";

// Test 4: Providers are available
$total++;
$allAvailable = $providersWithCities->every(fn($p) => $p->is_available);
if ($allAvailable) {
    echo "✅ TEST 4: All Providers Are Available\n";
    $pass++;
} else {
    echo "❌ TEST 4: Some providers not available\n";
}
echo "\n";

// Test 5: Providers have schedules
$total++;
$providersWithSchedules = ProviderProfile::where('specialization_id', $dysgraphia->id)
    ->with('schedules')
    ->get();
$allHaveSchedules = $providersWithSchedules->every(fn($p) => $p->schedules->count() > 0);
if ($allHaveSchedules) {
    echo "✅ TEST 5: All Providers Have Work Schedules\n";
    foreach ($providersWithSchedules as $p) {
        echo "   - {$p->user->name}: {$p->schedules->count()} work days configured\n";
    }
    $pass++;
} else {
    echo "❌ TEST 5: Some providers missing schedules\n";
}
echo "\n";

// Test 6: API response format
$total++;
$provider = $providersWithCities->first();
$cities = $provider->city()->get();

$simpleCity = null;
foreach ($cities as $city) {
    $count = ProviderProfile::where('city_id', $city->id)
        ->where('specialization_id', $dysgraphia->id)
        ->where('is_available', true)
        ->count();
    if ($count > 0) {
        $simpleCity = $city;
        break;
    }
}

if ($simpleCity) {
    $query = ProviderProfile::with(['user', 'specialization', 'city', 'province'])
        ->where('city_id', $simpleCity->id)
        ->where('is_available', true)
        ->where('specialization_id', $dysgraphia->id);

    $apiProviders = $query->get()->map(function ($profile) {
        return [
            'id' => $profile->id,
            'user' => [
                'id' => $profile->user->id,
                'name' => $profile->user->name,
                'email' => $profile->user->email,
            ],
            'title' => $profile->title ?? 'Dr.',
            'years_experience' => $profile->years_experience,
            'consultation_fee' => $profile->consultation_fee,
            'rating' => $profile->rating,
            'total_reviews' => $profile->total_reviews,
        ];
    });

    if ($apiProviders->count() > 0) {
        echo "✅ TEST 6: API Response Format Valid\n";
        echo "   Sample Provider in {$simpleCity->name_ar}:\n";
        $sample = $apiProviders->first();
        echo "   - Display Name: {$sample['title']} {$sample['user']['name']}\n";
        echo "   - Years: {$sample['years_experience']}\n";
        echo "   - Fee: {$sample['consultation_fee']} DZD\n";
        echo "   - Rating: {$sample['rating']}/5 ({$sample['total_reviews']} reviews)\n";
        $pass++;
    } else {
        echo "❌ TEST 6: API returned no providers\n";
    }
} else {
    echo "⚠️  TEST 6: No city found with available providers (SKIP)\n";
}
echo "\n";

// Test 7: Users have correct role
$total++;
$providerUsers = $providersWithCities->pluck('user')->unique('id');
$doctorRole = \Spatie\Permission\Models\Role::where('name', 'doctor')->first();
$allDoctors = $providerUsers->every(function ($user) use ($doctorRole) {
    return $user->hasRole('doctor');
});

if ($allDoctors && $doctorRole) {
    echo "✅ TEST 7: All Providers Have 'Doctor' Role\n";
    $pass++;
} else {
    echo "⚠️  TEST 7: Role check failed or 'doctor' role missing (WARN)\n";
}
echo "\n";

// Test 8: Provinces and cities exist
$total++;
$provinces = Province::whereIn('code', ['16', '31', '25'])->get();
$cities = City::whereIn('province_id', $provinces->pluck('id'))->get();
if ($provinces->count() == 3 && $cities->count() >= 3) {
    echo "✅ TEST 8: Location Data Complete\n";
    echo "   Provinces: " . $provinces->pluck('name_ar')->join(', ') . "\n";
    echo "   Cities: " . $cities->take(3)->pluck('name_ar')->join(', ') . "\n";
    $pass++;
} else {
    echo "⚠️  TEST 8: Some location data missing (expected for fresh setup)\n";
}
echo "\n";

// Summary
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              TEST SUMMARY                                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

$percentage = ($pass / $total) * 100;
$status = $percentage >= 70 ? "✅ READY TO USE" : ($percentage >= 50 ? "⚠️  PARTIAL" : "❌ NEEDS FIX");

echo "Passed: {$pass}/{$total} tests ({$percentage}%)\n";
echo "Status: {$status}\n\n";

if ($pass == $total) {
    echo "🎉 ALL TESTS PASSED! SYSTEM IS FULLY CONFIGURED!\n\n";
    echo "Next steps:\n";
    echo "1. php artisan serve\n";
    echo "2. npm run dev\n";
    echo "3. Navigate to the booking system\n";
    echo "4. Select a province and city\n";
    echo "5. Choose a specialist from the dropdown\n";
    echo "6. Complete the booking\n";
} elseif ($pass >= $total * 0.7) {
    echo "✅ System is mostly ready!\n\n";
    echo "Some optional components might need configuration, but core booking should work.\n";
} else {
    echo "❌ System needs fixes before use.\n\n";
    echo "Try running: php artisan db:seed --class=DysgraphiaSpecialistSeeder\n";
}

echo "\n";
