<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class PassportTypeSeeder extends Seeder
{
    public function run(): void
    {
        Gender::insert([
            ['name' => 'China Identity Card'],
            ['name' => 'Hong Kong Identity Card'],
            ['name' => 'Macau Identity Card'],
        ]);
    }
}
