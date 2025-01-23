<?php

namespace Database\Factories;

use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'district_id' => District::inRandomOrder()->first()->id,
            'address' => fake()->streetAddress(),
        ];
    }
}
