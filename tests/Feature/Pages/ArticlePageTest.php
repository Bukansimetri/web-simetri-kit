<?php

namespace Tests\Feature\Pages;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_index_shows_published_articles_newest_first(): void
    {
        Article::factory()->create(['title' => 'Artikel Lama', 'published_at' => now()->subDays(10)]);
        Article::factory()->create(['title' => 'Artikel Baru', 'published_at' => now()->subDay()]);

        $response = $this->get('/artikel');

        $response->assertOk();
        $response->assertSeeInOrder(['Artikel Baru', 'Artikel Lama'], escape: false);
    }

    public function test_article_index_shows_empty_state_when_no_articles(): void
    {
        $response = $this->get('/artikel');

        $response->assertOk();
        $response->assertSee('Belum ada artikel', escape: false);
    }

    public function test_article_show_displays_detail(): void
    {
        $article = Article::factory()->create(['title' => 'Panduan Perawatan Panel Surya']);

        $response = $this->get('/artikel/'.$article->slug);

        $response->assertOk();
        $response->assertSee('Panduan Perawatan Panel Surya', escape: false);
    }

    public function test_article_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->get('/artikel/artikel-tidak-ada');

        $response->assertNotFound();
    }
}
