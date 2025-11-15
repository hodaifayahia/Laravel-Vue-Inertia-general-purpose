<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Http\Controllers\LocationController;

$controller = new LocationController();

try {
    $response = $controller->index();
    $data = $response->getData(true);
    
    echo "✅ API Status: SUCCESS\n\n";
    echo "Response structure:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "Provinces count: " . (isset($data['provinces']) ? count($data['provinces']) : 'N/A') . "\n";
    echo "Cities count: " . (isset($data['cities']) ? count($data['cities']) : 'N/A') . "\n";
    echo "Specializations count: " . (isset($data['specializations']) ? count($data['specializations']) : 'N/A') . "\n";
    
} catch (\Exception $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
