<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AdmissionTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'testing_at' => fake()->dateTime(),
            'location_id' => Location::inRandomOrder()->first() ?? Location::factory()->create(),
            'maximum_candidates' => fake()->randomNumber(),
            'is_public' => fake()->randomElement([true, false]),
        ];
    }
}
