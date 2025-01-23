<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'address_id' => Address::inRandomOrder()->first() ?? Address::factory()->create(),
            'name' => fake()->company(),
        ];
    }
}
