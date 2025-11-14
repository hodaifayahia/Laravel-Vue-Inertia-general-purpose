<?php
/**
 * This script parses the algeria_cities.sql file and converts it to JSON format
 * for provinces and cities.
 * 
 * Usage: php parse_sql_to_json.php [path/to/algeria_cities.sql]
 */

$sqlFile = $argc > 1 ? $argv[1] : __DIR__ . '/algeria_cities.sql';

if (!file_exists($sqlFile)) {
    echo "Error: SQL file not found at {$sqlFile}\n";
    exit(1);
}

echo "Reading SQL file: {$sqlFile}\n";
$sqlContent = file_get_contents($sqlFile);

// Arrays to store data
$provinces = [];
$provincesMap = []; // To track unique provinces by code
$cities = [];

// Parse INSERT statements - the SQL file uses pattern:
// INSERT INTO algeria_cities(...) VALUES (id,'commune_name','commune_name_ascii','daira_name','daira_name_ascii','wilaya_code','wilaya_name','wilaya_name_ascii');

if (preg_match_all(
    "/INSERT INTO algeria_cities\([^)]+\)\s*VALUES\s*\(([^;]+)\);/i",
    $sqlContent,
    $matches
)) {
    foreach ($matches[1] as $valuesString) {
        // Parse the values: id, commune_name, commune_name_ascii, daira_name, daira_name_ascii, wilaya_code, wilaya_name, wilaya_name_ascii
        if (preg_match(
            "/(\d+)\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'/",
            $valuesString,
            $rowMatch
        )) {
            $id = $rowMatch[1];
            $communeNameAr = $rowMatch[2];
            $communeNameEn = $rowMatch[3];
            $dairaNameAr = $rowMatch[4];
            $dairaNameEn = $rowMatch[5];
            $wilayaCode = str_pad(trim($rowMatch[6]), 2, '0', STR_PAD_LEFT);
            $wilayaNameAr = trim($rowMatch[7]);
            $wilayaNameEn = $rowMatch[8];
            
            // Add province if not already added
            if (!isset($provincesMap[$wilayaCode])) {
                $provinces[] = [
                    'code' => $wilayaCode,
                    'name_ar' => $wilayaNameAr,
                    'name_en' => $wilayaNameEn
                ];
                $provincesMap[$wilayaCode] = true;
            }
            
            // Add city
            $cities[] = [
                'province_code' => $wilayaCode,
                'name_ar' => $communeNameAr,
                'name_en' => $communeNameEn
            ];
        }
    }
}

// Sort provinces by code
usort($provinces, function($a, $b) {
    return (int)$a['code'] - (int)$b['code'];
});

// Sort cities by province_code, then by name_ar
usort($cities, function($a, $b) {
    $codeCompare = (int)$a['province_code'] - (int)$b['province_code'];
    if ($codeCompare !== 0) {
        return $codeCompare;
    }
    return strcmp($a['name_ar'], $b['name_ar']);
});

// Output results
echo "\nParsing Results:\n";
echo "===============\n";
echo "Found " . count($provinces) . " provinces (wilayas)\n";
echo "Found " . count($cities) . " cities (communes)\n\n";

// Save to JSON files
$provincesFile = __DIR__ . '/database/seeders/data/provinces.json';
$citiesFile = __DIR__ . '/database/seeders/data/cities.json';

// Create backup of existing files
if (file_exists($provincesFile)) {
    $backup = $provincesFile . '.backup.' . date('YmdHis');
    copy($provincesFile, $backup);
    echo "Backup created: {$backup}\n";
}

if (file_exists($citiesFile)) {
    $backup = $citiesFile . '.backup.' . date('YmdHis');
    copy($citiesFile, $backup);
    echo "Backup created: {$backup}\n";
}

file_put_contents($provincesFile, json_encode($provinces, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents($citiesFile, json_encode($cities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\nFiles saved:\n";
echo "  Provinces: {$provincesFile}\n";
echo "  Cities: {$citiesFile}\n\n";

// Display all provinces
echo "All Provinces (Wilayas):\n";
echo "=======================\n";
foreach ($provinces as $p) {
    echo sprintf("  [%s] %s / %s\n", $p['code'], $p['name_ar'], $p['name_en']);
}

echo "\nFirst 15 cities:\n";
echo "================\n";
foreach (array_slice($cities, 0, 15) as $c) {
    echo "  [{$c['province_code']}] {$c['name_ar']} / {$c['name_en']}\n";
}

echo "\nDone! You can now run the seeder to update the database.\n";
echo "Command: php artisan db:seed --class=AlgeriaSeeder\n";
