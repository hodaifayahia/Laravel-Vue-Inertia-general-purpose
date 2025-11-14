<?php
/**
 * Reset Algeria provinces and cities data
 * This will truncate the existing data and reseed with the correct data
 * 
 * Usage: php reset_algeria_data.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "===========================================\n";
echo "Reset Algeria Provinces and Cities Data\n";
echo "===========================================\n\n";

try {
    // Disable foreign key checks
    echo "Disabling foreign key checks...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Truncate cities first (because of foreign key)
    echo "Truncating cities table...\n";
    DB::table('cities')->truncate();
    
    // Truncate provinces
    echo "Truncating provinces table...\n";
    DB::table('provinces')->truncate();
    
    // Re-enable foreign key checks
    echo "Re-enabling foreign key checks...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "\n✓ Tables truncated successfully!\n\n";
    
    // Run the seeder
    echo "Running AlgeriaSeeder...\n";
    echo "===========================================\n";
    Artisan::call('db:seed', ['--class' => 'AlgeriaSeeder']);
    echo Artisan::output();
    
    // Display statistics
    $provincesCount = DB::table('provinces')->count();
    $citiesCount = DB::table('cities')->count();
    
    echo "\n===========================================\n";
    echo "✓ Data Reset Complete!\n";
    echo "===========================================\n";
    echo "Provinces: {$provincesCount}\n";
    echo "Cities: {$citiesCount}\n";
    
    // Show some sample data
    echo "\nFirst 10 provinces:\n";
    $provinces = DB::table('provinces')->orderBy('code')->limit(10)->get();
    foreach ($provinces as $p) {
        echo "  [{$p->code}] {$p->name_ar} / {$p->name_en}\n";
    }
    
    echo "\nSample cities from Ghardaia (code 47):\n";
    $ghardaia = DB::table('provinces')->where('code', '47')->first();
    if ($ghardaia) {
        $cities = DB::table('cities')->where('province_id', $ghardaia->id)->limit(10)->get();
        foreach ($cities as $c) {
            echo "  {$c->name_ar} / {$c->name_en}\n";
        }
    }
    
    echo "\n✓ All done!\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
