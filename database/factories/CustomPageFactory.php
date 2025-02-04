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
            'title' => fake()->sentence(),
            'og_image_url' => fake()->imageUrl(630, 1200),
            'description' => fake()->sentence(2),
            'content' => fake()->paragraph(3, true),
        ];
    }
}
