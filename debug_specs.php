<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\Specialization;
use App\Models\City;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking all specializations...\n";
$specs = Specialization::all();
foreach ($specs as $spec) {
    echo "- ID: {$spec->id}, Name: {$spec->name}, Slug: {$spec->slug}\n";
}

echo "\nChecking all providers...\n";
$providers = ProviderProfile::with('user', 'specialization', 'city')->get();
foreach ($providers as $p) {
    echo "- ID: {$p->id}, User: {$p->user->name}, Spec ID: {$p->specialization_id}, Spec: " . ($p->specialization ? $p->specialization->name : 'NULL') . ", City: " . ($p->city ? $p->city->name_ar : 'NULL') . "\n";
}

echo "\nChecking Dysgraphia spec ID = 1 (if it exists)...\n";
$dysgraphia_id_1 = ProviderProfile::where('specialization_id', 1)->with('specialization')->get();
echo "Count: " . count($dysgraphia_id_1) . "\n";
foreach ($dysgraphia_id_1 as $p) {
    echo "- {$p->user->name} has spec_id 1, actual spec: " . ($p->specialization ? $p->specialization->name : 'NULL') . "\n";
}
