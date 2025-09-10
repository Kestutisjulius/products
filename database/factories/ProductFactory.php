<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->bothify('SKU###')),
            'description' => fake()->sentence(),
            'size' => fake()->word(),
            'photo' => '/images/test.jpg',
        ];
    }

}
