<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\CustomPage;
use App\Models\PortfolioProject;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HasSeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_seo_uses_custom_fields_when_filled(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'meta_title' => 'Judul SEO Kustom',
            'meta_description' => 'Deskripsi SEO kustom.',
            'meta_image_path' => 'seo/custom.webp',
        ]);

        $this->assertSame('Judul SEO Kustom', $product->seoTitle());
        $this->assertSame('Deskripsi SEO kustom.', $product->seoDescription());
        $this->assertSame(Storage::disk('public')->url('seo/custom.webp'), $product->seoImageUrl());
    }

    public function test_product_seo_falls_back_to_content_fields_when_empty(): void
    {
        $product = Product::factory()->create([
            'name' => 'Panel Surya 550W',
            'short_description' => 'Panel surya efisiensi tinggi untuk rumah tangga.',
            'images' => ['products/cover.webp'],
            'meta_title' => null,
            'meta_description' => null,
            'meta_image_path' => null,
        ]);

        $this->assertSame('Panel Surya 550W', $product->seoTitle());
        $this->assertSame('Panel surya efisiensi tinggi untuk rumah tangga.', $product->seoDescription());
        $this->assertSame($product->coverImageUrl(), $product->seoImageUrl());
    }

    public function test_product_seo_image_is_null_when_no_image_anywhere(): void
    {
        $product = Product::factory()->create(['images' => [], 'meta_image_path' => null]);

        $this->assertNull($product->seoImageUrl());
    }

    public function test_article_seo_falls_back_to_excerpt_and_image_path(): void
    {
        Storage::fake('public');

        $article = Article::factory()->create([
            'title' => 'Tips Hemat Listrik',
            'excerpt' => 'Cara menghemat tagihan listrik bulanan.',
            'image_path' => 'articles/cover.webp',
            'meta_title' => null,
            'meta_description' => null,
            'meta_image_path' => null,
        ]);

        $this->assertSame('Tips Hemat Listrik', $article->seoTitle());
        $this->assertSame('Cara menghemat tagihan listrik bulanan.', $article->seoDescription());
        $this->assertSame(Storage::disk('public')->url('articles/cover.webp'), $article->seoImageUrl());
    }

    public function test_article_seo_image_is_null_when_no_image_path(): void
    {
        $article = Article::factory()->create(['image_path' => null, 'meta_image_path' => null]);

        $this->assertNull($article->seoImageUrl());
    }

    public function test_custom_page_seo_description_strips_html_and_limits_length(): void
    {
        $customPage = CustomPage::factory()->create([
            'title' => 'Kebijakan Privasi',
            'content' => '<h2>Pendahuluan</h2><p>'.str_repeat('Lorem ipsum dolor sit amet. ', 20).'</p>',
            'meta_title' => null,
            'meta_description' => null,
        ]);

        $description = $customPage->seoDescription();

        $this->assertSame('Kebijakan Privasi', $customPage->seoTitle());
        $this->assertStringNotContainsString('<', $description);
        $this->assertLessThanOrEqual(163, strlen($description)); // Str::limit menambah "..."
    }

    public function test_custom_page_seo_image_is_always_null_without_custom_upload(): void
    {
        $customPage = CustomPage::factory()->create(['meta_image_path' => null]);

        $this->assertNull($customPage->seoImageUrl());
    }

    public function test_portfolio_project_seo_falls_back_to_description_and_cover_image(): void
    {
        Storage::fake('public');

        $project = PortfolioProject::factory()->create([
            'title' => 'Instalasi PLTS Atap Pabrik',
            'description' => '<p>Proyek instalasi 100kWp di pabrik tekstil.</p>',
            'images' => ['portfolio/cover.webp'],
            'meta_title' => null,
            'meta_description' => null,
            'meta_image_path' => null,
        ]);

        $this->assertSame('Instalasi PLTS Atap Pabrik', $project->seoTitle());
        $this->assertSame('Proyek instalasi 100kWp di pabrik tekstil.', $project->seoDescription());
        $this->assertSame($project->coverImageUrl(), $project->seoImageUrl());
    }
}
