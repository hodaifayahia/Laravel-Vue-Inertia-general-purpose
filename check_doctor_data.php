<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\ProviderProfile;

echo "=== Doctor/Provider Data Check ===\n\n";

$doctors = User::role('doctor')->with('providerProfile.specialization', 'providerProfile.city.province')->limit(5)->get();

echo "Total Doctors: " . User::role('doctor')->count() . "\n";
echo "Found " . $doctors->count() . " sample doctors\n\n";

foreach ($doctors as $i => $doctor) {
    echo "--- Doctor " . ($i+1) . " ---\n";
    echo "Name: " . $doctor->name . " (" . $doctor->email . ")\n";
    echo "Has ProviderProfile: " . ($doctor->providerProfile ? 'YES' : 'NO') . "\n";
    
    if ($doctor->providerProfile) {
        $p = $doctor->providerProfile;
        echo "  Title: " . ($p->title ?? 'N/A') . "\n";
        echo "  Years Experience: " . ($p->years_experience ?? 'N/A') . "\n";
        echo "  Specialization ID: " . ($p->specialization_id ?? 'NULL') . "\n";
        echo "  Specialization Name: " . ($p->specialization?->name_en ?? 'NULL') . "\n";
        echo "  City ID: " . ($p->city_id ?? 'NULL') . "\n";
        echo "  City Name: " . ($p->city?->name_en ?? 'NULL') . "\n";
        echo "  Province: " . ($p->city?->province?->name_en ?? 'NULL') . "\n";
        echo "  Latitude: " . ($p->latitude ?? 'NULL') . "\n";
        echo "  Longitude: " . ($p->longitude ?? 'NULL') . "\n";
    }
    echo "\n";
}

echo "=== Summary ===\n";
$profiles = ProviderProfile::whereNotNull('city_id')->count();
echo "Profiles with City: " . $profiles . " / " . ProviderProfile::count() . "\n";
$profiles = ProviderProfile::whereNotNull('specialization_id')->count();
echo "Profiles with Specialization: " . $profiles . " / " . ProviderProfile::count() . "\n";
?>
