<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlgeriaSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProvinces();
        $this->seedCities();
    }

    private function seedProvinces(): void
    {
        $file = database_path('seeders/data/provinces.json');
        if (!file_exists($file)) {
            $this->command->warn('Provinces JSON not found at ' . $file . '; skipping.');
            return;
        }

        $provinces = json_decode(file_get_contents($file), true);
        if (!$provinces) {
            $this->command->warn('Provinces JSON invalid or empty; skipping.');
            return;
        }

        DB::transaction(function () use ($provinces) {
            foreach ($provinces as $p) {
                Province::updateOrCreate(
                    ['code' => $p['code']],
                    ['code' => $p['code'], 'name_ar' => $p['name_ar'], 'name_en' => $p['name_en']]
                );
            }
        });

        $this->command->info('Provinces dataset seeded.');
    }

    private function seedCities(): void
    {
        $file = database_path('seeders/data/cities.json');
        if (!file_exists($file)) {
            $this->command->warn('Cities JSON not found at ' . $file . '; skipping.');
            return;
        }

        $cities = json_decode(file_get_contents($file), true);
        if (!$cities) {
            $this->command->warn('Cities JSON invalid or empty; skipping.');
            return;
        }

        DB::transaction(function () use ($cities) {
            foreach ($cities as $c) {
                $province = Province::where('code', $c['province_code'])->first();
                if (!$province) {
                    $this->command->warn("Province code {$c['province_code']} not found for city {$c['name_ar']}");
                    continue;
                }

                City::updateOrCreate(
                    ['province_id' => $province->id, 'name_ar' => $c['name_ar']],
                    [
                        'province_id' => $province->id,
                        'name_ar' => $c['name_ar'],
                        'name_en' => $c['name_en'] ?? $c['name_ar']
                    ]
                );
            }
        });

        $this->command->info('Cities dataset seeded.');
    }
}
