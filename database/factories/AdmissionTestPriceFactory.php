<?php

namespace Database\Factories;

use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionTestPrice>
 */
class AdmissionTestPriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => AdmissionTestProduct::inRandomOrder()->first()->id ?? AdmissionTestProduct::factory()->create()->id,
            'name' => fake()->randomElement([true, false]) ? fake()->word() : null,
            'value' => fake()->numberBetween(1, 65535),
        ];
    }
}
