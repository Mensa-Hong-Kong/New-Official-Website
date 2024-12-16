<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserHasContactFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $contactType = Arr::random(['email', 'mobile']);
        $contact = '';
        switch ($contactType) {
            case 'email':
                $contact = fake()->email();
                break;
            case 'mobile':
                $contact = fake()->phoneNumber();
                break;
        }

        return [
            'user_id' => User::inRandomOrder()->first(),
            'type' => $contactType,
            'contact' => $contact,
        ];
    }
}
