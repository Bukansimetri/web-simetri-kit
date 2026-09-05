<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'title' => $title,
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(4, true),
            'redaksi' => 'Tim Redaksi SUOER',
            'image_path' => null,
            'article_category_id' => ArticleCategory::factory(),
            'published_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }
}
