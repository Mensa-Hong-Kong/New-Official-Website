<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AdmissionTestTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'interval_month' => fake()->numberBetween(0, 60),
            'is_active' => fake()->randomElement([true, false]),
            'display_order' => fake()->randomElement([true, false]),
        ];
    }
}
