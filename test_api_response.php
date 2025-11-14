<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\City;
use App\Models\Specialization;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║             API RESPONSE TEST FOR BOOKING SYSTEM                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

// Get the Dysgraphia specialization
$dysgraphia = Specialization::where('slug', 'dysgraphia')->first();
echo "Dysgraphia Specialization ID: {$dysgraphia->id}\n\n";

// Get all Dysgraphia providers
$dysgraphiaProviders = ProviderProfile::where('specialization_id', $dysgraphia->id)
    ->with(['user', 'specialization', 'city', 'province'])
    ->get();

echo "Total Dysgraphia Providers: {$dysgraphiaProviders->count()}\n";
echo "Locations:\n";
$locations = $dysgraphiaProviders->pluck('city')->unique('id');
foreach ($locations as $city) {
    echo "  - {$city->name_ar} (ID: {$city->id})\n";
}
echo "\n";

// Test API response for each city
foreach ($locations as $city) {
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Testing API: /api/providers?city_id={$city->id}&specialization=dysgraphia\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    // Simulate the API call
    $query = ProviderProfile::with(['user', 'specialization', 'city', 'province'])
        ->where('city_id', $city->id)
        ->where('is_available', true);
    
    if (true) { // specialization = dysgraphia
        $query->where('specialization_id', $dysgraphia->id);
    }
    
    $providers = $query->get()->map(function ($profile) {
        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'user' => [
                'id' => $profile->user->id,
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'avatar' => $profile->user->avatar ?? null,
            ],
            'title' => $profile->title ?? 'Dr.',
            'specialization' => $profile->specialization->name ?? 'N/A',
            'bio' => $profile->bio,
            'years_experience' => $profile->years_experience,
            'slot_duration' => $profile->slot_duration,
            'consultation_fee' => $profile->consultation_fee,
            'rating' => $profile->rating,
            'total_reviews' => $profile->total_reviews,
            'total_patients' => $profile->total_patients ?? 0,
            'city' => [
                'id' => $profile->city->id,
                'name_ar' => $profile->city->name_ar,
                'name_en' => $profile->city->name_en,
            ],
            'province' => [
                'id' => $profile->province->id,
                'name_ar' => $profile->province->name_ar,
                'name_en' => $profile->province->name_en,
            ],
        ];
    });
    
    echo "Found: {$providers->count()} provider(s)\n";
    echo json_encode($providers->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}

echo "✅ API test completed successfully!\n";
echo "\nExpected Vue component to receive:\n";
echo "  - provider.id (for bookings)\n";
echo "  - provider.user.name (for dropdown display)\n";
echo "  - provider.title (for dropdown display: 'Dr. Name')\n";
echo "  - provider.years_experience\n";
echo "  - provider.consultation_fee\n";
echo "  - provider.rating & provider.total_reviews\n";
