<?php

namespace Database\Factories;

use App\Models\NavigationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class NavigationItemFactory extends Factory
{
    public function definition(): array
    {
        $masterIDs = NavigationItem::get('id')->pluck('id')->toArray();
        $masterIDs[] = null;
        $masterID = fake()->randomElement($masterIDs);
        if ($masterID) {
            $maxDisplayOrder = NavigationItem::where('master_id', $masterID);
        } else {
            $maxDisplayOrder = NavigationItem::whereNull('master_id');
        }
        $maxDisplayOrder = $maxDisplayOrder->max('display_order');

        return [
            'master_id' => $masterID,
            'name' => fake()->word(),
            'url' => fake()->randomElement([true, false]) ? fake()->url() : null,
            'display_order' => fake()->numberBetween(0, $maxDisplayOrder),
        ];
    }
}
