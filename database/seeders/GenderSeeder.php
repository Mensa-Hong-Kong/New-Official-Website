<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    public function run(): void
    {
        Gender::insert([
            ['name' => 'Male'],
            ['name' => 'Female'],
        ]);
    }
}
