<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProviderProfile;
use App\Models\Specialization;

echo "=== ProviderProfile and Specialization Check ===\n\n";

$profile = ProviderProfile::with('specialization')->first();
echo "Profile specialization_id: " . $profile->specialization_id . "\n";
echo "Profile specialization relation: " . ($profile->specialization ? 'EXISTS' : 'NULL') . "\n";

if ($profile->specialization) {
    echo "Specialization ID: " . $profile->specialization->id . "\n";
    echo "Specialization name: " . $profile->specialization->name . "\n";
}

echo "\n=== Direct query ===\n";
$spec = Specialization::find($profile->specialization_id);
echo "Found: " . ($spec ? $spec->name : 'NOT FOUND') . "\n";

echo "\n=== Check if table has missing data ===\n";
$count = Specialization::whereNull('name')->count();
echo "Specializations with NULL name: " . $count . "\n";
?>
