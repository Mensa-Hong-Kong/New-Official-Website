<?php

namespace Database\Seeders;

use App\Models\PassportType;
use Illuminate\Database\Seeder;

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
