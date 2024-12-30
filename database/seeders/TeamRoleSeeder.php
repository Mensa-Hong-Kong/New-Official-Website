<?php

namespace Database\Seeders;

use App\Models\TeamRole;
use Illuminate\Database\Seeder;

class TeamRoleSeeder extends Seeder
{
    public function run(): void
    {
        TeamRole::create(['name' => 'Super Administrator']);
    }
}
