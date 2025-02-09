<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class CustomPageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pathname' => implode(
                '/', fake()->words(
                    fake()->numberBetween(1, 4)
                )
            ),
            'title' => fake()->sentence(1),
            'og_image_url' => fake()->randomElement([true, false]) ? fake()->imageUrl(630, 1200) : null,
            'description' => fake()->sentence(2),
            'content' => fake()->paragraph(3, true),
        ];
    }
}
