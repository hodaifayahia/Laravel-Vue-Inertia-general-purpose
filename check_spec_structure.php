#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use App\Models\Specialization;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spec = Specialization::first();
echo "Specialization columns:\n";
if ($spec) {
    print_r($spec->getAttributes());
}

echo "\n\nAll specializations:\n";
$all = Specialization::all();
foreach ($all as $s) {
    echo "ID {$s->id}: {$s->name}\n";
}
