<?php

namespace Tests\Unit\Seo;

use App\Models\Article;
use App\Models\FaqItem;
use App\Models\Product;
use App\Support\Seo\JsonLd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class JsonLdTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_returns_null_when_items_are_empty(): void
    {
        $this->assertNull(JsonLd::faqPage(new Collection));
    }

    public function test_faq_page_maps_items_to_question_and_answer(): void
    {
        $items = collect([
            FaqItem::factory()->make(['question' => 'Apa itu panel surya?', 'answer' => 'Perangkat penghasil listrik dari cahaya matahari.']),
            FaqItem::factory()->make(['question' => 'Berapa lama garansi?', 'answer' => '25 tahun untuk performa panel.']),
        ]);

        $schema = JsonLd::faqPage($items);

        $this->assertSame('FAQPage', $schema['@type']);
        $this->assertCount(2, $schema['mainEntity']);
        $this->assertSame('Apa itu panel surya?', $schema['mainEntity'][0]['name']);
        $this->assertSame('Perangkat penghasil listrik dari cahaya matahari.', $schema['mainEntity'][0]['acceptedAnswer']['text']);
        $this->assertSame('Question', $schema['mainEntity'][0]['@type']);
        $this->assertSame('Answer', $schema['mainEntity'][0]['acceptedAnswer']['@type']);
    }

    public function test_article_schema_contains_required_fields(): void
    {
        $article = Article::factory()->make([
            'title' => 'Tips Hemat Listrik',
            'image_path' => 'articles/cover.webp',
            'published_at' => now(),
            'redaksi' => 'Tim Redaksi SUOER',
        ]);

        $schema = JsonLd::article($article);

        $this->assertSame('Article', $schema['@type']);
        $this->assertSame($article->seoTitle(), $schema['headline']);
        $this->assertSame($article->seoImageUrl(), $schema['image']);
        $this->assertSame('Tim Redaksi SUOER', $schema['author']['name']);
        $this->assertNotEmpty($schema['datePublished']);
    }

    public function test_product_schema_includes_offers_price_only_when_price_is_filled(): void
    {
        $withPrice = Product::factory()->make(['price' => 5000000]);
        $withoutPrice = Product::factory()->make(['price' => null]);

        $schemaWithPrice = JsonLd::product($withPrice);
        $schemaWithoutPrice = JsonLd::product($withoutPrice);

        $this->assertSame('Product', $schemaWithPrice['@type']);
        $this->assertArrayHasKey('offers', $schemaWithPrice);
        $this->assertSame('5000000.00', $schemaWithPrice['offers']['price']);

        $this->assertArrayNotHasKey('offers', $schemaWithoutPrice);
    }
}
