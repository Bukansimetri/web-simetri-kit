<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\Category;
use App\Models\FaqItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_has_faq_page_schema_when_items_exist(): void
    {
        FaqItem::factory()->create(['question' => 'Apakah bisa cicilan?', 'answer' => 'Bisa, lewat mitra pembiayaan kami.']);

        $response = $this->get('/faq');

        $response->assertOk();
        $response->assertSee('"@type":"FAQPage"', escape: false);
        $response->assertSee('Apakah bisa cicilan?', escape: false);
    }

    public function test_faq_page_has_no_faq_page_schema_when_no_items(): void
    {
        $response = $this->get('/faq');

        $response->assertOk();
        $response->assertDontSee('FAQPage', escape: false);
    }

    public function test_article_page_has_article_schema(): void
    {
        $article = Article::factory()->create(['published_at' => now()->subDay()]);

        $response = $this->get('/artikel/'.$article->slug);

        $response->assertOk();
        $response->assertSee('"@type":"Article"', escape: false);
    }

    public function test_product_page_with_price_has_offers_in_schema(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 3500000]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee('"@type":"Product"', escape: false);
        $response->assertSee('"offers"', escape: false);
    }

    /**
     * `price` wajib diisi di skema `products` saat ini (kolom NOT NULL) —
     * jadi kasus "produk tanpa harga" tidak bisa direproduksi lewat DB nyata
     * di sini. Cabang null-price `JsonLd::product()` tetap diuji di level
     * unit (JsonLdTest, via factory `make()` yang tidak menyentuh DB), untuk
     * jaga-jaga bila skema harga berubah longgar di masa depan (FR-011).
     */
}
