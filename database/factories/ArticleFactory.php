<?php

namespace Database\Factories;

use App\Models\Article;
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
            'image_path' => 'articles/placeholder.jpg',
            'category' => fake()->randomElement(['tips', 'berita', 'edukasi']),
            'published_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }
}
