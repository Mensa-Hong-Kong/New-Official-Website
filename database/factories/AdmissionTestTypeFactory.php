<?php

namespace Database\Factories;

use App\Models\AdmissionTestType;
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
            'display_order' => (AdmissionTestType::max('display_order') ?? 0) + 1,
        ];
    }
}
