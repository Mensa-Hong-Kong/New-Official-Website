<?php

namespace Database\Seeders;

use App\Models\ContactType;
use Illuminate\Database\Seeder;

class ContactTypeSeeder extends Seeder
{
    public function run(): void
    {
        ContactType::firstOrCreate([
            'name' => 'email',
            'url' => 'mailto:',
            'can_verify' => true,
        ]);

        ContactType::firstOrCreate([
            'name' => 'mobile',
            'url' => 'https://wa.me/',
            'can_verify' => true,
        ]);
    }
}
