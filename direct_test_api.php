<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Api\PublicApiController;

$controller = new PublicApiController();
$response = $controller->getDoctors();

echo $response->getContent();
?>
