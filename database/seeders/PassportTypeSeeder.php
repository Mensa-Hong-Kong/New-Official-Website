<?php

namespace Database\Seeders;

use App\Models\PassportType;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the passport_types table with predefined data.
 * The 'passport_types' table will contain:
 * | id  | name                    | created_at | updated_at |
 * | --- | ----------------------- | ---------- | ---------- |
 * | 1   | China Identity Card     | ...        | ...        |
 * | 2   | Hong Kong Identity Card | ...        | ...        |
 * | 3   | Macau Identity Card     | ...        | ...        |
 */
class PassportTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'China Identity Card'],
            ['name' => 'Hong Kong Identity Card'],
            ['name' => 'Macau Identity Card'],
        ];
        foreach ($rows as $row) {
            PassportType::firstOrCreate($row);
        }
    }
}
