#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\ProviderProfile;
use App\Models\City;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Bonoura doctors...\n\n";

// Get all Bonoura providers
$bonouraProviders = ProviderProfile::where('user_id', '>=', 13)
    ->where('user_id', '<=', 22)
    ->with('user', 'city')
    ->get();

echo "Bonoura Providers (User IDs 13-22):\n";
foreach ($bonouraProviders as $p) {
    echo "- {$p->user->name}: City ID = {$p->city_id}, City Name = " . ($p->city ? $p->city->name_ar : 'NULL') . "\n";
}

echo "\n\nAll cities with 'بونورة':\n";
$cities = City::where('name_ar', 'بونورة')->orWhere('name_en', 'like', '%Bounoura%')->orWhere('name_en', 'like', '%Bonoura%')->get();
foreach ($cities as $city) {
    echo "- ID: {$city->id}, Name: {$city->name_ar} ({$city->name_en}), Province: {$city->province_id}\n";
}

echo "\n\nAll cities with 'بومرداس':\n";
$cities2 = City::where('name_ar', 'بومرداس')->get();
foreach ($cities2 as $city) {
    echo "- ID: {$city->id}, Name: {$city->name_ar} ({$city->name_en})\n";
}
