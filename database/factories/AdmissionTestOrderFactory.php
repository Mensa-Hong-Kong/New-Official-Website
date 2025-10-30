<?php

namespace Database\Factories;

use App\Models\OtherPaymentGateway;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AdmissionTestOrderFactory extends Factory
{
    public function definition(): array
    {
        $expiredAtTimestamp = fake()->dateTimeBetween(
            now()->addWeek(),
            now()->addYear()
        )->getTimestamp();
        $expiredAt = Carbon::createFromTimeStamp($expiredAtTimestamp);

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'product_name' => fake()->randomElement([true, false]) ? fake()->word() : null,
            'price_name' => fake()->randomElement([true, false]) ? fake()->word() : null,
            'price' => fake()->numberBetween(1, 65535),
            'quota' => fake()->numberBetween(1, 255),
            'status' => fake()->randomElement(['pending', 'cancelled', 'succeeded']),
            'expired_at' => $expiredAt,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
            'reference_number' => fake()->randomElement([true, false]) ? fake()->uuid() : null,
        ];
    }
}
