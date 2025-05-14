<?php

namespace Database\Factories;

use App\Models\Gender;
use App\Models\PassportType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $passportType = PassportType::inRandomOrder()->first();
        switch ($passportType->id) {
            case 1: // Chinese
                $passportNumber = fake()->numerify(str_repeat('#', 17));
                break;
            case 2: // Hong Kong
                $prefixes = [
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
                    'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'V', 'W', 'Y', 'Z',
                    'XA', 'XB', 'XC', 'XD', 'XE', 'XF', 'XG', 'XH',
                ];
                $passportNumber = fake()->randomElement($prefixes).fake()->randomNumber(7, true);
                break;
            case 3: // Macao
                $passportNumber = fake()->randomNumber(8, true);
                break;
        }

        return [
            'username' => Str::random(8),
            'password' => static::$password ??= Hash::make('password'),
            'family_name' => fake()->lastName(),
            'middle_name' => fake()->randomElement([true, false]) ? fake()->lastName() : null,
            'given_name' => fake()->firstName(),
            'passport_type_id' => $passportType->id,
            'passport_number' => $passportNumber,
            'gender_id' => Gender::inRandomOrder()->first()->id,
            'birthday' => fake()->date(),
            'remember_token' => Str::random(10),
        ];
    }
}
