<?php

namespace Database\Seeders;

use App\Models\PassportType;
use Illuminate\Database\Seeder;

class PassportTypeSeeder extends Seeder
{
    public function run(): void
    {
        PassportType::insert([
            ['name' => 'China Identity Card'],
            ['name' => 'Hong Kong Identity Card'],
            ['name' => 'Macau Identity Card'],
        ]);
    }
}
