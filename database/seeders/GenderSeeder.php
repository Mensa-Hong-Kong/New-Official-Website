<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Male', 'Female'];
        foreach ($names as $name) {
            Gender::create(['name' => $name]);
        }
    }
}
