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
    public function definition(): array
    {
        $contactType = Arr::random(['email', 'mobile']);
        $contact = '';
        switch ($contactType) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }

        return [
            'user_id' => User::inRandomOrder()->first(),
            'type' => $contactType,
            'contact' => $contact,
        ];
    }

    public function email(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'email',
                'contact' => fake()->freeEmail(),
            ];
        });
    }

    public function mobile(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'mobile',
                'contact' => fake()->numberBetween(10000, 999999999999999),
            ];
        });
    }
}
