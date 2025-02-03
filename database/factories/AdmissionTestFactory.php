<?php

namespace Database\Factories;

use App\Models\Address;
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
            'testing_at' => fake()->dateTimeBetween(
                now()->addWeek(),
                now()->addYear()
            ),
            'location_id' => fake()->randomElement([true, false]) ?
                Location::factory()->create() :
                Location::inRandomOrder()->first() ?? Location::factory()->create(),
            'address_id' => fake()->randomElement([true, false]) ?
                Address::factory()->create() :
                Address::inRandomOrder()->first() ?? Address::factory()->create(),
            'maximum_candidates' => fake()->numberBetween(1, 10000),
            'is_public' => fake()->randomElement([true, false]),
        ];
    }
}
