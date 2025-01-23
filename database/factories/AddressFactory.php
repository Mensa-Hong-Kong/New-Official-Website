<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\District;
use App\Models\Gender;
use App\Models\Location;
use App\Models\PassportType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'district_id' => District::inRandomOrder()->first()->id,
            'address' => fake()->streetAddress(),
        ];
    }
}
