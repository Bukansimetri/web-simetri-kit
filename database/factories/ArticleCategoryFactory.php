<?php

namespace Database\Factories;

use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleCategory>
 */
class ArticleCategoryFactory extends Factory
{
    protected $model = ArticleCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'order' => 0,
        ];
    }
}
