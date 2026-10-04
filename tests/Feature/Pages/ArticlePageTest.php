<?php

namespace Tests\Feature\Pages;

use App\Models\Article;
use App\Models\ArticleCategory;
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

    public function test_index_uses_hero_banner_grid_and_sidebar(): void
    {
        Article::factory()->create(['title' => 'Artikel Pertama']);

        $this->get('/artikel')
            ->assertOk()
            ->assertSee('Wawasan & Artikel')
            ->assertSee('images/mockup/artikel-3.jpg')
            ->assertSee('Cari Artikel')
            ->assertSee('Update Mingguan')
            ->assertSee('Langganan')
            ->assertSee('Baca Selengkapnya')
            ->assertSee('Punya pertanyaan seputar', escape: false);
    }

    public function test_every_published_article_is_in_the_grid_with_card_details(): void
    {
        $category = ArticleCategory::factory()->create(['name' => 'Teknologi']);
        Article::factory()->create([
            'title' => 'Satu',
            'excerpt' => 'Ringkasan satu.',
            'article_category_id' => $category->id,
            'published_at' => now()->subDay(),
        ]);
        Article::factory()->create(['title' => 'Dua', 'published_at' => now()->subDays(2)]);

        $this->get('/artikel')
            ->assertOk()
            ->assertSeeInOrder(['Satu', 'Dua'])
            ->assertSee('Teknologi')
            ->assertSee('Ringkasan satu.');
    }

    public function test_draft_and_scheduled_articles_are_hidden(): void
    {
        Article::factory()->create(['title' => 'Terbit', 'published_at' => now()->subDay()]);
        Article::factory()->create(['title' => 'Draf Rahasia', 'published_at' => null]);
        Article::factory()->create(['title' => 'Terjadwal Rahasia', 'published_at' => now()->addDay()]);

        $this->get('/artikel?q=Rahasia')
            ->assertOk()
            ->assertDontSee('Draf Rahasia')
            ->assertDontSee('Terjadwal Rahasia');
    }

    public function test_search_matches_title_or_excerpt_and_keeps_term_visible(): void
    {
        Article::factory()->create(['title' => 'Inverter Hybrid', 'excerpt' => 'Panduan umum.']);
        Article::factory()->create(['title' => 'Baterai Rumah', 'excerpt' => 'Membahas inverter dan daya.']);
        Article::factory()->create(['title' => 'Panel Atap', 'excerpt' => 'Tidak terkait.']);

        $this->get('/artikel?q=inverter')
            ->assertOk()
            ->assertSee('Inverter Hybrid')
            ->assertSee('Baterai Rumah')
            ->assertDontSee('Panel Atap')
            ->assertSee('value="inverter"', escape: false)
            ->assertSee('Hapus pencarian');
    }

    public function test_search_without_match_shows_not_found_message_and_sidebar(): void
    {
        Article::factory()->create(['title' => 'Panel Atap']);

        $this->get('/artikel?q=zzzz')
            ->assertOk()
            ->assertSee('tidak ditemukan')
            ->assertSee('Hapus pencarian')
            ->assertDontSee('Panel Atap')
            ->assertSee('Update Mingguan');
    }

    public function test_search_results_are_not_leaked_between_terms_through_cache(): void
    {
        Article::factory()->create(['title' => 'Alpha Artikel']);
        Article::factory()->create(['title' => 'Beta Artikel']);

        $this->get('/artikel?q=alpha')->assertSee('Alpha Artikel')->assertDontSee('Beta Artikel');
        $this->get('/artikel?q=beta')->assertSee('Beta Artikel')->assertDontSee('Alpha Artikel');
        $this->get('/artikel')->assertSee('Alpha Artikel')->assertSee('Beta Artikel');
    }

    public function test_search_treats_wildcards_literally_and_handles_long_or_array_input(): void
    {
        Article::factory()->create(['title' => 'Diskon 50% Panel']);
        Article::factory()->create(['title' => 'Artikel Biasa']);

        $this->get('/artikel?q='.urlencode('50%'))
            ->assertOk()
            ->assertSee('Diskon 50% Panel')
            ->assertDontSee('Artikel Biasa');

        $this->get('/artikel?q='.urlencode('%'))->assertOk()->assertDontSee('Artikel Biasa');
        $this->get('/artikel?q='.str_repeat('a', 500))->assertOk();
        $this->get('/artikel?q[]=x')->assertOk()->assertSee('Artikel Biasa');
    }

    public function test_search_term_is_escaped_in_output(): void
    {
        $this->get('/artikel?q='.urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', escape: false);
    }

    public function test_category_filter_buttons_remain_available(): void
    {
        $category = ArticleCategory::factory()->create(['name' => 'Edukasi']);
        Article::factory()->create(['article_category_id' => $category->id]);

        $this->get('/artikel')->assertOk()->assertSee('Edukasi')->assertSee('Semua');
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
