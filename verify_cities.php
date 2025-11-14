<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$provinces = DB::table('provinces')->count();
$cities = DB::table('cities')->count();

echo "✅ Database Statistics:\n";
echo "   Provinces: {$provinces}\n";
echo "   Cities: {$cities}\n\n";

echo "📋 Sample Cities (first 10):\n";
$sample = DB::table('cities')
    ->join('provinces', 'cities.province_id', '=', 'provinces.id')
    ->select('cities.name_ar', 'cities.name_en', 'provinces.name_ar as province_ar')
    ->limit(10)
    ->get();

foreach($sample as $city) {
    echo "   - {$city->name_ar} ({$city->name_en}) in {$city->province_ar}\n";
}

echo "\n📊 Cities Count by Province (Top 10):\n";
$stats = DB::table('cities')
    ->join('provinces', 'cities.province_id', '=', 'provinces.id')
    ->select('provinces.name_ar', 'provinces.code', DB::raw('COUNT(*) as city_count'))
    ->groupBy('provinces.id', 'provinces.name_ar', 'provinces.code')
    ->orderByDesc('city_count')
    ->limit(10)
    ->get();

foreach($stats as $stat) {
    echo "   [{$stat->code}] {$stat->name_ar}: {$stat->city_count} communes\n";
}

// Check for any remaining placeholder names
$placeholders = DB::table('cities')
    ->where('name_en', 'LIKE', 'Municipality %')
    ->orWhere('name_en', 'LIKE', 'City %')
    ->count();

if ($placeholders > 0) {
    echo "\n⚠️  WARNING: Found {$placeholders} placeholder city names!\n";
} else {
    echo "\n✅ SUCCESS: No placeholder names found - all cities have real names!\n";
}
