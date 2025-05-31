<?php

namespace Database\Factories;

use App\Models\OtherPaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class OtherPaymentGatewayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'display_order' => OtherPaymentGateway::max('display_order') + 1,
        ];
    }
}
