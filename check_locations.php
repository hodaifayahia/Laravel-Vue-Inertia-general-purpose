<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Records ===\n";
echo "Provinces: " . \App\Models\Province::count() . "\n";
echo "Cities: " . \App\Models\City::count() . "\n\n";

echo "=== Sample Provinces ===\n";
\App\Models\Province::limit(5)->get()->each(function ($p) {
    echo "  [{$p->code}] {$p->name_ar} ({$p->name_en}) - {$p->cities->count()} cities\n";
});

echo "\n=== Sample Cities ===\n";
\App\Models\City::limit(10)->get()->each(function ($c) {
    echo "  {$c->name_ar} ({$c->name_en}) - Province: {$c->province->name_ar}\n";
});
