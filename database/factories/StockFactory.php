<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'city' => fake()->city(),
            'stock' => fake()->numberBetween(1, 100),
            'product_id' => null,
        ];
    }
}
