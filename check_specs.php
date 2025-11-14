<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$specializations = \App\Models\Specialization::all(['id', 'name']);
echo json_encode($specializations->toArray(), JSON_PRETTY_PRINT);
