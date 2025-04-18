<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\AdmissionTestType;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AdmissionTestFactory extends Factory
{
    public function definition(): array
    {
        $testingAtTimestamp = fake()->dateTimeBetween(
            now()->addWeek(),
            now()->addYear()
        )->getTimestamp();
        $testingAt = Carbon::createFromTimeStamp($testingAtTimestamp);
        $type = AdmissionTestType::first() ?? AdmissionTestType::factory()->create();

        return [
            'type_id' => $type->id,
            'testing_at' => $testingAt->format('Y-m-d H:i:s'),
            'expect_end_at' => $testingAt->addMinutes(30)->format('Y-m-d H:i:s'),
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
