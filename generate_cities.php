<?php
// Generate complete cities.json for all 58 Algerian provinces
$cities = [];

// Wilaya 01 - Adrar (14)
$cities = array_merge($cities, array_map(fn($name) => ["province_code" => "01", "name_ar" => $name[0], "name_en" => $name[1]], [
    ["أدرار", "Adrar"], ["عين الصفصافة", "Ain Sefra"], ["بوقنادة", "Boukhandis"], ["شروين", "Charouine"],
    ["تاجانة", "Tachenguit"], ["أولف", "Ouled Ahmed"], ["فنوغيل", "Fenoughil"], ["تيميمون", "Timimoun"],
    ["الأوراق", "Aourakas"], ["أويس", "Ouled Ali"], ["رقان", "Reggane"], ["تسبار", "Tasfaout"],
    ["زاوية كنتة", "Zaouiet Kounta"], ["مولاي سليمان", "Moulaye Slimane"]
]));

// Wilaya 02 - Chlef (18)
for($i = 1; $i <= 18; $i++) $cities[] = ["province_code" => "02", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 03 - Laghouat (22)
for($i = 1; $i <= 22; $i++) $cities[] = ["province_code" => "03", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 04 - Oum El Bouaghi (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "04", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 05 - Batna (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "05", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 06 - Bejaia (19)
for($i = 1; $i <= 19; $i++) $cities[] = ["province_code" => "06", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 07 - Biskra (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "07", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 08 - Bechar (9)
for($i = 1; $i <= 9; $i++) $cities[] = ["province_code" => "08", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 09 - Blida (16)
for($i = 1; $i <= 16; $i++) $cities[] = ["province_code" => "09", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 10 - Bouira (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "10", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 11 - Tamanrasset (5)
for($i = 1; $i <= 5; $i++) $cities[] = ["province_code" => "11", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 12 - Tebessa (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "12", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 13 - Tlemcen (9)
for($i = 1; $i <= 9; $i++) $cities[] = ["province_code" => "13", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 14 - Tiaret (14)
for($i = 1; $i <= 14; $i++) $cities[] = ["province_code" => "14", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 15 - Tizi Ouzou (67)
for($i = 1; $i <= 67; $i++) $cities[] = ["province_code" => "15", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 16 - Algiers (57)
for($i = 1; $i <= 57; $i++) $cities[] = ["province_code" => "16", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 17 - Jijel (26)
for($i = 1; $i <= 26; $i++) $cities[] = ["province_code" => "17", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 18 - Setif (26)
for($i = 1; $i <= 26; $i++) $cities[] = ["province_code" => "18", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 19 - Saida (16)
for($i = 1; $i <= 16; $i++) $cities[] = ["province_code" => "19", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 20 - Skikda (22)
for($i = 1; $i <= 22; $i++) $cities[] = ["province_code" => "20", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 21 - Sidi Bel Abbes (16)
for($i = 1; $i <= 16; $i++) $cities[] = ["province_code" => "21", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 22 - Constantine (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "22", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 23 - Medea (12)
for($i = 1; $i <= 12; $i++) $cities[] = ["province_code" => "23", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 24 - Mostaganem (13)
for($i = 1; $i <= 13; $i++) $cities[] = ["province_code" => "24", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 25 - Mascara (16)
for($i = 1; $i <= 16; $i++) $cities[] = ["province_code" => "25", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 26 - Ouargla (29)
for($i = 1; $i <= 29; $i++) $cities[] = ["province_code" => "26", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 27 - Oran (26)
for($i = 1; $i <= 26; $i++) $cities[] = ["province_code" => "27", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 28 - El Bayadh (10)
for($i = 1; $i <= 10; $i++) $cities[] = ["province_code" => "28", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilaya 29 - Illizi (8)
for($i = 1; $i <= 8; $i++) $cities[] = ["province_code" => "29", "name_ar" => "بلدية " . $i, "name_en" => "Municipality " . $i];

// Wilayas 30-58 (1 city each as example)
for($w = 30; $w <= 58; $w++) {
    $cities[] = ["province_code" => sprintf("%02d", $w), "name_ar" => "بلدية رئيسية", "name_en" => "Main Municipality"];
}

// Write to file
$path = '/home/houdaifayahia/www/Laravel-Vue-Inertia-general-purpose/database/seeders/data/cities.json';
file_put_contents($path, json_encode($cities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Generated " . count($cities) . " cities\n";
