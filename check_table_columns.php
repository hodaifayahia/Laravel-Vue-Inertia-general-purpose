<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\City;
use App\Models\Specialization;

echo "=== City Columns ===\n";
$columns = \DB::getSchemaBuilder()->getColumnListing('cities');
echo implode(", ", $columns) . "\n\n";

echo "=== Sample City ===\n";
$city = City::first();
if ($city) {
    foreach ($columns as $col) {
        echo "$col: " . $city->$col . "\n";
    }
}

echo "\n=== Specialization Columns ===\n";
$columns = \DB::getSchemaBuilder()->getColumnListing('specializations');
echo implode(", ", $columns) . "\n\n";

echo "=== Sample Specialization ===\n";
$spec = Specialization::first();
if ($spec) {
    foreach ($columns as $col) {
        echo "$col: " . $spec->$col . "\n";
    }
}
?>
