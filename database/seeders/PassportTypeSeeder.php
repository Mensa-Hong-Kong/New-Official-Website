<?php

namespace Database\Seeders;

use App\Models\PassportType;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the passport_types table with predefined data.
 * The 'passport_types' table will contain:
 * | id  | name                    | display_order | created_at | updated_at |
 * | --- | ----------------------- | ------------- | ---------- | ---------- |
 * | 1   | China Identity Card     | 1             | ...        | ...        |
 * | 2   | Hong Kong Identity Card | 3             | ...        | ...        |
 * | 3   | Macau Identity Card     | 2             | ...        | ...        |
 */
class PassportTypeSeeder extends Seeder
{
    public function run(): void
    {
        PassportType::updateOrCreate(
            ['name' => 'China Identity Card'],
            ['display_order' => 1]
        );
        PassportType::updateOrCreate(
            ['name' => 'Hong Kong Identity Card'],
            ['display_order' => 3]
        );
        PassportType::updateOrCreate(
            ['name' => 'Macau Identity Card'],
            ['display_order' => 2]
        );
    }
}
