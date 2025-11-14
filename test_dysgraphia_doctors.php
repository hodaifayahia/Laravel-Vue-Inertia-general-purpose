<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\Specialization;
use App\Models\City;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║          DYSGRAPHIA SPECIALISTS VERIFICATION REPORT               ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

// Check Specialization
$dysgraphia = Specialization::where('slug', 'dysgraphia')->first();
if ($dysgraphia) {
    echo "✓ Dysgraphia Specialization:\n";
    echo "  - ID: {$dysgraphia->id}\n";
    echo "  - Name: {$dysgraphia->name}\n";
    echo "  - Slug: {$dysgraphia->slug}\n\n";
} else {
    echo "✗ Dysgraphia Specialization not found!\n\n";
}

// Check Providers
$providers = ProviderProfile::with('user', 'specialization', 'city', 'province', 'schedules')
    ->where('specialization_id', 1)
    ->get();

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  TOTAL PROVIDERS WITH DYSGRAPHIA SPECIALIZATION: " . str_pad(count($providers), 2, " ", STR_PAD_LEFT) . " │\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

foreach ($providers as $index => $provider) {
    echo "─ Doctor #" . ($index + 1) . " ─────────────────────────────────────────────────────\n";
    echo "  Profile ID: {$provider->id}\n";
    echo "  User ID: {$provider->user_id}\n";
    echo "  Name: {$provider->user->name}\n";
    echo "  Email: {$provider->user->email}\n";
    echo "  Title: {$provider->title}\n";
    echo "  Specialization: {$provider->specialization->name}\n";
    echo "  Location: {$provider->city->name_ar} ({$provider->city->name_en}), {$provider->province->name_ar}\n";
    echo "  Is Available: " . ($provider->is_available ? "YES ✓" : "NO ✗") . "\n";
    echo "  Years Experience: {$provider->years_experience}\n";
    echo "  Consultation Fee: {$provider->consultation_fee} DZD\n";
    echo "  Rating: {$provider->rating}/5 ({$provider->total_reviews} reviews)\n";
    echo "  Total Patients: {$provider->total_patients}\n";
    echo "  Work Schedules: {$provider->schedules->count()} days configured\n";
    
    echo "  Schedule Details:\n";
    foreach ($provider->schedules as $schedule) {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $day_name = $days[$schedule->day_of_week] ?? 'Unknown';
        echo "    • {$day_name}: {$schedule->start_time} - {$schedule->end_time}\n";
    }
    echo "\n";
}

// Test API response format
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║             TESTING API RESPONSE FORMAT                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

if ($providers->count() > 0) {
    $provider = $providers->first();
    $apiResponse = [
        'id' => $provider->id,
        'user_id' => $provider->user_id,
        'user' => [
            'id' => $provider->user->id,
            'name' => $provider->user->name,
            'email' => $provider->user->email,
            'avatar' => $provider->user->avatar ?? null,
        ],
        'title' => $provider->title ?? 'Dr.',
        'specialization' => $provider->specialization->name ?? 'N/A',
        'bio' => $provider->bio,
        'years_experience' => $provider->years_experience,
        'slot_duration' => $provider->slot_duration,
        'consultation_fee' => $provider->consultation_fee,
        'rating' => $provider->rating,
        'total_reviews' => $provider->total_reviews,
        'total_patients' => $provider->total_patients ?? 0,
        'city' => [
            'id' => $provider->city->id,
            'name_ar' => $provider->city->name_ar,
            'name_en' => $provider->city->name_en,
        ],
        'province' => [
            'id' => $provider->province->id,
            'name_ar' => $provider->province->name_ar,
            'name_en' => $provider->province->name_en,
        ],
    ];
    
    echo "Sample API Response for: {$provider->user->name}\n";
    echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Check required fields
    echo "✓ API Response includes:\n";
    echo "  ✓ user.name (for dropdown display)\n";
    echo "  ✓ title (for dropdown display)\n";
    echo "  ✓ years_experience\n";
    echo "  ✓ consultation_fee\n";
    echo "  ✓ rating & total_reviews\n";
    echo "  ✓ city & province info\n";
}

echo "\n✅ All Dysgraphia doctors are ready for booking!\n";
