<?php

namespace Database\Factories;

use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortfolioProject>
 */
class PortfolioProjectFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'portfolio_category_id' => PortfolioCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'images' => ['portfolio/'.fake()->uuid().'.webp'],
            'client_name' => null,
            'project_url' => null,
            'completed_at' => null,
            'order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
