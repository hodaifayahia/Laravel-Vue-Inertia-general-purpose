<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Http\Controllers\Api\PublicApiController;

$controller = new PublicApiController();

try {
    $response = $controller->getDoctors();
    $data = $response->getData(true);
    
    echo "✅ API Status: SUCCESS\n";
    echo "Total doctors: " . count($data['doctors']) . "\n";
    echo "\nFirst 3 doctors:\n";
    
    foreach (array_slice($data['doctors'], 0, 3) as $doctor) {
        echo "\n- {$doctor['name']}\n";
        echo "  Specialty: {$doctor['specialty']}\n";
        echo "  City: {$doctor['city_name']}\n";
        echo "  Province: {$doctor['province_name']}\n";
        echo "  Years experience: {$doctor['years_experience']}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
