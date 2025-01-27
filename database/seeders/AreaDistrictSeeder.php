<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\District;
use Illuminate\Database\Seeder;

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
