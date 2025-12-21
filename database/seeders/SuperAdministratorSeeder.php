<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * This seeder populated the users table with a super administrator account.
 * 
 * The 'users' table will contain:
 * | id  | username   | password | family_name | middle_name | given_name | gender_id | passport_type_id | passport_number    | birthday   | synced_to_stripe | remember_token | created_at | updated_at |
 * | --- | ---------- | -------- | ----------- | ----------- | ---------- | --------- | ---------------- | ------------------ | ---------- | ---------------- | -------------- | ---------- | ---------- |
 * | 1   | superAdmin | password | Lam         |             | Mak        | 2         | 1                | 350321096003237001 | 0960-03-23 | 0                | ...            | ...        | ...        |
 */
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
