<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Gender;
use App\Models\Location;
use App\Models\PassportType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'address_id' => Address::inRandomOrder()->first() ?? Address::factory()->create(),
            'name' => fake()->company(),
        ];
    }
}
