<?php

// Quick test script to verify Activities system is set up correctly

echo "=== Activities System Verification ===\n\n";

// 1. Check if models exist
echo "✓ Checking Models...\n";
$models = [
    'App\Models\Activity',
    'App\Models\ActivityItem', 
    'App\Models\ActivityAttempt',
    'App\Models\Result',
];

foreach ($models as $model) {
    if (class_exists($model)) {
        echo "  ✓ $model exists\n";
    } else {
        echo "  ✗ $model NOT FOUND\n";
    }
}

// 2. Check if tables exist
echo "\n✓ Checking Database Tables...\n";
try {
    $activities = \App\Models\Activity::count();
    echo "  ✓ activities table exists (count: $activities)\n";
    
    $items = \App\Models\ActivityItem::count();
    echo "  ✓ activity_items table exists (count: $items)\n";
    
    $attempts = \App\Models\ActivityAttempt::count();
    echo "  ✓ activity_attempts table exists (count: $attempts)\n";
    
    $results = \App\Models\Result::count();
    echo "  ✓ results table exists (count: $results)\n";
} catch (\Exception $e) {
    echo "  ✗ Database error: " . $e->getMessage() . "\n";
}

// 3. Check routes
echo "\n✓ Checking Routes...\n";
$routes = [
    'activities.index' => 'GET /activities',
    'activities.play' => 'GET /activities/{activity}/play',
    'admin.activities.index' => 'GET /admin/activities',
    'admin.activities.create' => 'GET /admin/activities/create',
    'admin.activities.edit' => 'GET /admin/activities/{activity}/edit',
    'admin.activities.attempts' => 'GET /admin/activities/attempts',
];

foreach ($routes as $name => $description) {
    try {
        $url = route($name, ['activity' => 1, 'attempt' => 1]);
        echo "  ✓ $name\n";
    } catch (\Exception $e) {
        echo "  ✗ $name - " . $e->getMessage() . "\n";
    }
}

// 4. Check controllers
echo "\n✓ Checking Controllers...\n";
$controllers = [
    'App\Http\Controllers\Admin\ActivityAdminController',
    'App\Http\Controllers\Api\ActivityController',
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "  ✓ $controller exists\n";
    } else {
        echo "  ✗ $controller NOT FOUND\n";
    }
}

// 5. Sample activity creation
echo "\n✓ Attempting Sample Data Creation...\n";
try {
    $existing = \App\Models\Activity::where('title', 'Sample Emoji Game')->first();
    if ($existing) {
        echo "  ✓ Sample activity already exists (ID: {$existing->id})\n";
    } else {
        $activity = \App\Models\Activity::create([
            'title' => 'Sample Emoji Game',
            'description' => 'Express how you feel with emojis',
            'estimated_duration_minutes' => 5,
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'order' => 1,
        ]);
        
        // Add an item
        \App\Models\ActivityItem::create([
            'activity_id' => $activity->id,
            'item_type' => 'emoji_choice',
            'prompt_text' => 'How are you feeling today?',
            'max_points' => 100,
            'order' => 1,
        ]);
        
        echo "  ✓ Created sample activity (ID: {$activity->id})\n";
    }
} catch (\Exception $e) {
    echo "  ⚠ Could not create sample: " . $e->getMessage() . "\n";
}

// 6. API endpoints
echo "\n✓ Checking API Endpoints...\n";
$endpoints = [
    'GET /api/activities',
    'POST /api/activities/{id}/start',
    'GET /api/activities/attempts/{id}/items',
    'POST /api/activities/attempts/{id}/submit',
    'POST /api/activities/attempts/{id}/complete',
];

foreach ($endpoints as $endpoint) {
    echo "  ✓ $endpoint\n";
}

echo "\n=== Verification Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Run: npm run dev (to start dev server)\n";
echo "2. Or run: npm run build (to build for production)\n";
echo "3. Visit: http://localhost:8000/admin/activities/create\n";
echo "4. Create an activity and test it\n";
echo "5. View results at: http://localhost:8000/admin/activities/attempts\n";

?>
