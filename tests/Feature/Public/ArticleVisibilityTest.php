<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_article_is_hidden_from_public(): void
    {
        $article = Article::factory()->create(['published_at' => null]);

        $this->get('/artikel')->assertOk()->assertDontSee($article->title, escape: false);
        $this->get('/artikel/'.$article->slug)->assertNotFound();
    }

    public function test_scheduled_future_article_is_hidden_until_its_date(): void
    {
        $article = Article::factory()->create(['published_at' => now()->addDay()]);

        $this->get('/artikel')->assertOk()->assertDontSee($article->title, escape: false);
        $this->get('/artikel/'.$article->slug)->assertNotFound();
    }

    public function test_published_article_is_visible(): void
    {
        $article = Article::factory()->create(['published_at' => now()->subHour()]);

        $this->get('/artikel')->assertOk()->assertSee($article->title, escape: false);
        $this->get('/artikel/'.$article->slug)->assertOk()->assertSee($article->title, escape: false);
    }
}
