<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$providers = DB::table('provider_profiles')->get();

echo "Database Provider Profiles:\n";
echo "==========================\n";

foreach($providers as $p) {
    echo "ID: {$p->id}\n";
    echo "  User ID: {$p->user_id}\n";
    echo "  Province ID: " . ($p->province_id ?? 'NULL') . "\n";
    echo "  City ID: " . ($p->city_id ?? 'NULL') . "\n";
    echo "  Clinic: " . ($p->clinic_name ?? 'NULL') . "\n";
    echo "  Phone: " . ($p->phone ?? 'NULL') . "\n";
    echo "  Fee: " . ($p->consultation_fee ?? 'NULL') . "\n";
    echo "\n";
}