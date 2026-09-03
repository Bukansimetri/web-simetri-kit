<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'name' => $name,
            'category_id' => Category::factory(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(1_000_000, 20_000_000),
            'strikethrough_price' => null,
            'images' => [],
            'specs' => ['Daya Maksimum (Pmax)' => '550W'],
            'features' => [
                ['icon' => 'bolt', 'title' => 'Efisiensi Maksimal', 'description' => fake()->sentence()],
            ],
            'order' => 0,
        ];
    }
}
