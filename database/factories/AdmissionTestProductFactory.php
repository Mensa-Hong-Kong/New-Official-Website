<?php

namespace Database\Factories;

use App\Models\AdmissionTestProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionTestProduct>
 */
class AdmissionTestProductFactory extends Factory
{
    public function definition(): array
    {
        $minimumAge = fake()->numberBetween(1, 254);

        return [
            'name' => fake()->word(),
            'option_name' => fake()->word(),
            'minimum_age' => fake()->numberBetween(1, 60),
            'maximum_age' => fake()->numberBetween($minimumAge, 255),
            'quota' => fake()->numberBetween(1, 255),
        ];
    }
}
