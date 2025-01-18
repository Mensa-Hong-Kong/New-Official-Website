<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Male'],
            ['name' => 'Female'],
        ];
        foreach ($rows as $row) {
            Gender::firstOrCreate($row);
        }
    }
}
