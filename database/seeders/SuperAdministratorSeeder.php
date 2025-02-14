<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdministratorSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'username' => 'superAdmin',
            'password' => 'password',
            'family_name' => 'Lam',
            'middle_name' => '',
            'given_name' => 'Mak',
            'gender_id' => 2,
            'passport_type_id' => 1,
            'passport_number' => '350321096003237001',
            'birthday' => '0960-03-23',
        ]);
        $user->assignRole('Super Administrator');
    }
}
