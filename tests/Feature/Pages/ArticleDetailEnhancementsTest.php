<?php

namespace Tests\Feature\Pages;

use App\Models\Article;
use App\Models\ArticleRelatedProduct;
use App\Models\Product;
use App\Services\SubmissionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ArticleDetailEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_count_increases_per_visit_and_is_displayed(): void
    {
        $article = Article::factory()->create(['view_count' => 41]);

        $this->get('/artikel/'.$article->slug)->assertOk()->assertSee('42 kali dilihat');
        $this->get('/artikel/'.$article->slug)->assertOk()->assertSee('43 kali dilihat');

        $this->assertSame(43, $article->fresh()->view_count);
    }

    public function test_unpublished_article_is_404_and_does_not_count(): void
    {
        $draft = Article::factory()->create(['published_at' => null, 'view_count' => 5]);

        $this->get('/artikel/'.$draft->slug)->assertNotFound();

        $this->assertSame(5, $draft->fresh()->view_count);
    }

    public function test_caption_is_shown_only_when_filled(): void
    {
        $with = Article::factory()->create(['image_caption' => 'Panel surya di atap pabrik']);
        $without = Article::factory()->create(['image_caption' => null]);

        $this->get('/artikel/'.$with->slug)->assertOk()
            ->assertSee('<figcaption', escape: false)
            ->assertSee('Panel surya di atap pabrik');

        $this->get('/artikel/'.$without->slug)->assertOk()->assertDontSee('<figcaption', escape: false);
    }

    public function test_related_products_are_shown_in_order_capped_at_four_and_deleted_ones_ignored(): void
    {
        $article = Article::factory()->create();
        $products = collect(['Produk Satu', 'Produk Dua', 'Produk Tiga', 'Produk Empat', 'Produk Lima'])
            ->map(fn (string $name) => Product::factory()->create(['name' => $name, 'images' => []]));

        $order = [3, 0, 4, 1, 2];
        foreach ($order as $sort => $index) {
            ArticleRelatedProduct::query()->create(['article_id' => $article->id, 'product_id' => $products[$index]->id, 'sort_order' => $sort]);
        }

        $this->get('/artikel/'.$article->slug)->assertOk()
            ->assertSee('Produk Terkait')
            ->assertSeeInOrder(['Produk Empat', 'Produk Satu', 'Produk Lima', 'Produk Dua'])
            ->assertDontSee('Produk Tiga');

        $products[3]->delete();

        $this->get('/artikel/'.$article->slug)->assertOk()->assertDontSee('Produk Empat');
    }

    public function test_related_products_section_is_hidden_when_empty(): void
    {
        $article = Article::factory()->create();

        $this->get('/artikel/'.$article->slug)->assertOk()->assertDontSee('Produk Terkait');
    }

    public function test_sidebar_shows_five_latest_other_articles_and_newsletter_card(): void
    {
        $current = Article::factory()->create(['title' => 'Artikel Yang Dibaca', 'published_at' => now()->subDays(30)]);
        foreach (range(1, 7) as $i) {
            Article::factory()->create(['title' => "Terbaru {$i}", 'published_at' => now()->subHours($i)]);
        }
        Article::factory()->create(['title' => 'Draf Tersembunyi', 'published_at' => null]);

        $html = $this->get('/artikel/'.$current->slug)->assertOk()->getContent();
        $sidebar = substr($html, strpos($html, 'Artikel Terbaru'));

        $this->assertStringContainsString('Terbaru 1', $sidebar);
        $this->assertStringContainsString('Terbaru 5', $sidebar);
        $this->assertStringNotContainsString('Terbaru 6', $sidebar);
        $this->assertStringNotContainsString('Draf Tersembunyi', $sidebar);
        $this->assertStringContainsString('Update Mingguan', $html);
        $this->assertStringContainsString('id="langganan"', $html);
    }

    public function test_subscribing_from_the_detail_page_redirects_back_to_it(): void
    {
        $article = Article::factory()->create();
        $detailUrl = url('/artikel/'.$article->slug);

        $this->from($detailUrl)->post('/langganan', [
            'email' => 'pembaca@example.com',
            'form_token' => Crypt::encryptString((string) now()->subSeconds(10)->timestamp),
        ])->assertRedirect($detailUrl.'#langganan');

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'pembaca@example.com']);
        $this->assertNotEmpty(SubmissionGuard::issueToken());
    }

    public function test_subscribing_from_an_external_referrer_falls_back_to_the_article_list(): void
    {
        $this->from('https://situs-lain.test/halaman')->post('/langganan', [
            'email' => 'luar@example.com',
            'form_token' => Crypt::encryptString((string) now()->subSeconds(10)->timestamp),
        ])->assertRedirect(url('/artikel').'#langganan');
    }
}
