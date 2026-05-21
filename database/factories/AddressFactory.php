<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => fake()->unique()->streetAddress(),
        ];
    }
}
