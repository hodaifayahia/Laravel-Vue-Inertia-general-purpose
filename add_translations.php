<?php

$file = __DIR__ . '/lang/php_en.json';
$translations = json_decode(file_get_contents($file), true);

// Add new keys
$newKeys = [
    'welcome.featured_specialists' => 'Featured Specialists',
    'welcome.meet_our_specialists' => 'Meet our experienced specialists ready to help',
    'welcome.years_experience' => 'years experience',
    'welcome.view_profile' => 'View Profile',
    'welcome.view_all_specialists' => 'View All Specialists',
];

foreach ($newKeys as $key => $value) {
    if (!isset($translations[$key])) {
        $translations[$key] = $value;
        echo "✓ Added: $key\n";
    } else {
        echo "- Skipped (exists): $key\n";
    }
}

// Sort keys alphabetically
ksort($translations);

// Save back to file
file_put_contents($file, json_encode($translations));
echo "\n✅ Translations updated successfully!\n";
echo "Total keys: " . count($translations) . "\n";
