#!/usr/bin/env python3
import json

# Read the complete Algeria cities data
with open('/home/houdaifayahia/www/Laravel-Vue-Inertia-general-purpose/temp_algeria_data/json/algeria_cities.json', 'r', encoding='utf-8') as f:
    all_cities = json.load(f)

# Convert to our format
cities_data = []
for city in all_cities:
    cities_data.append({
        "province_code": city['wilaya_code'],
        "name_ar": city['commune_name'],
        "name_en": city['commune_name_ascii']
    })

# Save to our cities.json file
output_path = '/home/houdaifayahia/www/Laravel-Vue-Inertia-general-purpose/database/seeders/data/cities.json'
with open(output_path, 'w', encoding='utf-8') as f:
    json.dump(cities_data, f, ensure_ascii=False, indent=2)

print(f"✓ Successfully converted {len(cities_data)} communes from algeria-cities repository")
print(f"✓ Saved to: {output_path}")

# Show statistics by province
from collections import Counter
province_counts = Counter([city['province_code'] for city in cities_data])
print(f"\n📊 Cities per Province (Top 10):")
for code, count in sorted(province_counts.items())[:10]:
    wilaya_name = next((c['wilaya_name'] for c in all_cities if c['wilaya_code'] == code), 'Unknown')
    print(f"   [{code}] {wilaya_name}: {count} communes")

print(f"\n✅ Total: {len(cities_data)} communes across 58 provinces")
