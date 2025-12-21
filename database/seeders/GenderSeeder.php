<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the genders table with predefined data as follows:
 * | id  | name   | created_at | updated_at |
 * | --- | ------ | ---------- | ---------- |
 * | 1   | Male   | ...        | ...        |
 * | 2   | Female | ...        | ...        |
 */
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
