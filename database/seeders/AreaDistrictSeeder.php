<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\District;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the areas and their corresponding districts in Hong Kong. The
 * 'areas' and 'districts' tables are filled with predefined data as follows:
 * 
 * The 'areas' table will contain:
 * | id  | name            | display_order | created_at | updated_at |
 * | --- | --------------- | ------------- | ---------- | ---------- |
 * | 1   | Hong Kong       | 0             | ...        | ...        |
 * | 2   | Kowloon         | 0             | ...        | ...        |
 * | 3   | New Territories | 0             | ...        | ...        |
 * 
 * The 'districts' table will contain:
 * | id  | area_id | name                | display_order | created_at | updated_at |
 * | --- | ------- | ------------------- | ------------- | ---------- | ---------- |
 * | 1   | 1       | Central and Western | 0             | ...        | ...        |
 * | 2   | 1       | Wan Chai            | 1             | ...        | ...        |
 * | 3   | 1       | Eastern             | 2             | ...        | ...        |
 * | 4   | 1       | Southern            | 3             | ...        | ...        |
 * | 5   | 2       | Yau Tsim Mong       | 0             | ...        | ...        |
 * | 6   | 2       | Sham Shui Po        | 1             | ...        | ...        |
 * | 7   | 2       | Kowloon City        | 2             | ...        | ...        |
 * | 8   | 2       | Wong Tai Sin        | 3             | ...        | ...        |
 * | 9   | 2       | Kwun Tong           | 4             | ...        | ...        |
 * | 10  | 3       | Kwai Tsing          | 0             | ...        | ...        |
 * | 11  | 3       | Tsuen Wan           | 1             | ...        | ...        |
 * | 12  | 3       | Tuen Mun            | 2             | ...        | ...        |
 * | 13  | 3       | Yuen Long           | 3             | ...        | ...        |
 * | 14  | 3       | North               | 4             | ...        | ...        |
 * | 15  | 3       | Tai Po              | 5             | ...        | ...        |
 * | 16  | 3       | Sha Tin             | 6             | ...        | ...        |
 * | 17  | 3       | Sai Kung            | 7             | ...        | ...        |
 * | 18  | 3       | Islands             | 8             | ...        | ...        |
 */
class AreaDistrictSeeder extends Seeder
{
    public function run(): void
    {
        $area = Area::firstOrCreate(['name' => 'Hong Kong']);
        $districts = [
            'Central and Western',
            'Wan Chai',
            'Eastern',
            'Southern',
        ];
        foreach ($districts as $index => $district) {
            $district = District::firstOrCreate([
                'area_id' => $area->id,
                'name' => $district,
            ]);
            $district->update(['display_order' => $index]);
        }
        $area = Area::firstOrCreate(['name' => 'Kowloon']);
        $districts = [
            'Yau Tsim Mong',
            'Sham Shui Po',
            'Kowloon City',
            'Wong Tai Sin',
            'Kwun Tong',
        ];
        foreach ($districts as $index => $district) {
            $district = District::firstOrCreate([
                'area_id' => $area->id,
                'name' => $district,
            ]);
            $district->update(['display_order' => $index]);
        }
        $area = Area::firstOrCreate(['name' => 'New Territories']);
        $districts = [
            'Kwai Tsing',
            'Tsuen Wan',
            'Tuen Mun',
            'Yuen Long',
            'North',
            'Tai Po',
            'Sha Tin',
            'Sai Kung',
            'Islands',
        ];
        foreach ($districts as $index => $district) {
            $district = District::firstOrCreate([
                'area_id' => $area->id,
                'name' => $district,
            ]);
            $district->update(['display_order' => $index]);
        }
    }
}
