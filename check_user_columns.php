<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== User Columns ===\n";
$columns = \DB::getSchemaBuilder()->getColumnListing('users');
foreach ($columns as $col) {
    echo "- " . $col . "\n";
}
?>
