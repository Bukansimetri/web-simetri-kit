<?php

namespace Database\Factories;

use App\Models\PortfolioCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortfolioCategory>
 */
class PortfolioCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'order' => 0,
        ];
    }
}
