<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Specialization;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Route;

// Test the API endpoint that loads providers by specialization
echo "🔍 Testing Provider API with Location Data\n";
echo "==========================================\n\n";

// Get the first specialization
$specialization = Specialization::first();
if (!$specialization) {
    echo "❌ No specializations found. Run the seeder first.\n";
    exit(1);
}

echo "📋 Testing specialization: {$specialization->name}\n\n";

// Simulate the API call that loads providers with location data
$providers = ProviderProfile::where('specialization_id', $specialization->id)
    ->with(['user', 'specialization', 'province', 'city', 'schedules'])
    ->get();

if ($providers->isEmpty()) {
    echo "❌ No providers found for this specialization.\n";
    exit(1);
}

foreach ($providers as $provider) {
    echo "👨‍⚕️ {$provider->user->name}\n";
    echo "   Specialization: {$provider->specialization->name}\n";

    if ($provider->province) {
        echo "   📍 Province: {$provider->province->name_ar} ({$provider->province->name_en}) - {$provider->province->code}\n";
    } else {
        echo "   ❌ No province data\n";
    }

    if ($provider->city) {
        echo "   🏛️ City: {$provider->city->name_ar} ({$provider->city->name_en})\n";
    } else {
        echo "   ❌ No city data\n";
    }

    if ($provider->clinic_name) {
        echo "   💼 Clinic: {$provider->clinic_name}\n";
    }

    echo "\n";
}

echo "✅ API test completed!\n";