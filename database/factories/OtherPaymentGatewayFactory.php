<?php

namespace Database\Factories;

use App\Models\OtherPaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OtherPaymentGateway>
 */
class OtherPaymentGatewayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'display_order' => OtherPaymentGateway::max('display_order') + 1,
        ];
    }
}
