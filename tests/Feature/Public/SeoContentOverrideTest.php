<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\CustomPage;
use App\Models\PortfolioProject;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoContentOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_uses_custom_seo_fields_when_filled(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seo/product-custom.webp', 'fake-bytes');

        $product = Product::factory()->create([
            'meta_title' => 'Judul SEO Produk Kustom',
            'meta_description' => 'Deskripsi SEO produk kustom.',
            'meta_image_path' => 'seo/product-custom.webp',
        ]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Judul SEO Produk Kustom', escape: false);
        $response->assertSee('Deskripsi SEO produk kustom.', escape: false);
        $response->assertSee(Storage::disk('public')->url('seo/product-custom.webp'), escape: false);
    }

    public function test_product_page_falls_back_to_content_fields_when_seo_empty(): void
    {
        $product = Product::factory()->create([
            'name' => 'Inverter Hybrid 5kW',
            'short_description' => 'Inverter hybrid untuk sistem panel surya rumahan.',
            'images' => [],
            'meta_title' => null,
            'meta_description' => null,
            'meta_image_path' => null,
        ]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Inverter Hybrid 5kW', escape: false);
        $response->assertSee('Inverter hybrid untuk sistem panel surya rumahan.', escape: false);
    }

    public function test_article_page_uses_custom_seo_fields_when_filled(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seo/article-custom.webp', 'fake-bytes');

        $article = Article::factory()->create([
            'meta_title' => 'Judul SEO Artikel Kustom',
            'meta_description' => 'Deskripsi SEO artikel kustom.',
            'meta_image_path' => 'seo/article-custom.webp',
        ]);

        $response = $this->get('/artikel/'.$article->slug);

        $response->assertOk();
        $response->assertSee('Judul SEO Artikel Kustom', escape: false);
        $response->assertSee('Deskripsi SEO artikel kustom.', escape: false);
        $response->assertSee(Storage::disk('public')->url('seo/article-custom.webp'), escape: false);
    }

    public function test_custom_page_uses_custom_seo_fields_when_filled(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seo/page-custom.webp', 'fake-bytes');

        $customPage = CustomPage::factory()->create([
            'meta_title' => 'Judul SEO Halaman Kustom',
            'meta_description' => 'Deskripsi SEO halaman kustom.',
            'meta_image_path' => 'seo/page-custom.webp',
        ]);

        $response = $this->get('/halaman/'.$customPage->slug);

        $response->assertOk();
        $response->assertSee('Judul SEO Halaman Kustom', escape: false);
        $response->assertSee('Deskripsi SEO halaman kustom.', escape: false);
        $response->assertSee(Storage::disk('public')->url('seo/page-custom.webp'), escape: false);
    }

    public function test_custom_page_falls_back_to_default_og_image_when_empty(): void
    {
        $customPage = CustomPage::factory()->create([
            'meta_title' => null,
            'meta_description' => null,
            'meta_image_path' => null,
        ]);

        $response = $this->get('/halaman/'.$customPage->slug);

        $response->assertOk();
        $response->assertSee($customPage->title, escape: false);
    }

    public function test_portfolio_project_page_uses_custom_seo_fields_when_filled(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seo/portfolio-custom.webp', 'fake-bytes');

        $project = PortfolioProject::factory()->create([
            'meta_title' => 'Judul SEO Portfolio Kustom',
            'meta_description' => 'Deskripsi SEO portfolio kustom.',
            'meta_image_path' => 'seo/portfolio-custom.webp',
        ]);

        $response = $this->get('/portfolio/'.$project->slug);

        $response->assertOk();
        $response->assertSee('Judul SEO Portfolio Kustom', escape: false);
        $response->assertSee('Deskripsi SEO portfolio kustom.', escape: false);
        $response->assertSee(Storage::disk('public')->url('seo/portfolio-custom.webp'), escape: false);
    }
}
