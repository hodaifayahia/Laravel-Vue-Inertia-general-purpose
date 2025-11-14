<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cities = DB::table('cities')->select('province_id', 'name_ar', 'name_en')->orderBy('province_id')->take(30)->get();

echo "\n=== Real Algerian Cities (First 30) ===\n";
foreach($cities as $city) {
    echo "  • " . $city->name_ar . " (" . $city->name_en . ")\n";
}

echo "\n=== Total Count ===\n";
$total_cities = DB::table('cities')->count();
$total_provinces = DB::table('provinces')->count();
echo "  Provinces: " . $total_provinces . "\n";
echo "  Cities: " . $total_cities . "\n";
