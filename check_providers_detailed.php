<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$providers = User::role('provider')->with('providerProfile.specialization', 'providerProfile.city.province')->get();

echo "Total Providers: " . $providers->count() . "\n\n";

if ($providers->count() > 0) {
    foreach ($providers->take(3) as $provider) {
        echo "Provider: " . $provider->name . "\n";
        echo "  Profile: " . ($provider->providerProfile ? 'EXISTS' : 'MISSING') . "\n";
        if ($provider->providerProfile) {
            echo "  City: " . ($provider->providerProfile->city?->name_en ?? 'N/A') . "\n";
            echo "  Province: " . ($provider->providerProfile->city?->province?->name_en ?? 'N/A') . "\n";
            echo "  Specialty: " . ($provider->providerProfile->specialization?->name_en ?? 'N/A') . "\n";
        }
        echo "\n";
    }
} else {
    echo "No providers found in database!\n";
    echo "Total Users: " . User::count() . "\n";
    echo "Users with roles: " . User::whereHas('roles')->count() . "\n";
}
?>
