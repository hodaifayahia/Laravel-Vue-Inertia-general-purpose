#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all routes
$routes = app('router')->getRoutes();

echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         API ROUTES CHECK                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Looking for API routes with '/api/providers':\n";
echo "─────────────────────────────────────────────────────────────────────────────────\n";

$found = false;
foreach ($routes as $route) {
    if (strpos($route->uri(), 'api/providers') !== false) {
        echo "✓ Found: " . $route->uri() . " (" . implode(', ', $route->methods()) . ")\n";
        $found = true;
    }
}

if (!$found) {
    echo "❌ No /api/providers routes found!\n";
}

echo "\n";
echo "All registered API routes:\n";
echo "─────────────────────────────────────────────────────────────────────────────────\n";

$count = 0;
foreach ($routes as $route) {
    if (strpos($route->uri(), 'api') === 0) {
        echo "• " . $route->uri() . " (" . implode(', ', $route->methods()) . ")\n";
        $count++;
    }
}

echo "\nTotal API routes: {$count}\n";

if ($count === 0) {
    echo "\n⚠️  No API routes found! Check api.php file.\n";
}
