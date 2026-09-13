<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\ClientLogo;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LazyLoadingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cari tag <img ...> pertama pada $html yang mengandung $needle.
     */
    private function imgTagContaining(string $html, string $needle): ?string
    {
        preg_match_all('/<img[^>]*>/', $html, $matches);

        foreach ($matches[0] as $tag) {
            if (str_contains($tag, $needle)) {
                return $tag;
            }
        }

        return null;
    }

    /**
     * @return array<int, string> seluruh tag <img ...> pada $html, urut sesuai dokumen.
     */
    private function allImgTags(string $html): array
    {
        preg_match_all('/<img[^>]*>/', $html, $matches);

        return $matches[0];
    }

    private function assertEager(?string $tag): void
    {
        $this->assertNotNull($tag, 'Tag <img> yang diharapkan tidak ditemukan.');
        $this->assertStringNotContainsString('loading="lazy"', $tag);
    }

    private function assertLazy(?string $tag): void
    {
        $this->assertNotNull($tag, 'Tag <img> yang diharapkan tidak ditemukan.');
        $this->assertStringContainsString('loading="lazy" decoding="async"', $tag);
    }

    public function test_home_page_hero_banner_is_eager_other_images_are_lazy(): void
    {
        Storage::fake('public');

        $banner = Banner::factory()->create(['alt_text' => 'Banner Promo Unik']);
        Storage::disk('public')->put($banner->image_path, 'fake-bytes');

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Produk Unggulan Unik', 'images' => ['products/cover.webp']]);
        Storage::disk('public')->put('products/cover.webp', 'fake-bytes');

        $testimonial = Testimonial::factory()->create(['name' => 'Testimoni Unik Sekali', 'photo_path' => 'testimonials/unik.webp']);
        Storage::disk('public')->put('testimonials/unik.webp', 'fake-bytes');

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertEager($this->imgTagContaining($content, 'Banner Promo Unik'));
        $this->assertLazy($this->imgTagContaining($content, 'Produk Unggulan Unik'));
        $this->assertLazy($this->imgTagContaining($content, 'Testimoni Unik Sekali'));
    }

    public function test_produk_index_hero_is_eager_and_product_cards_are_lazy(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Panel Surya Uji Coba', 'images' => ['products/cover.webp']]);

        $content = $this->get('/produk')->assertOk()->getContent();

        $this->assertEager($this->imgTagContaining($content, 'mockup/produk-1.jpg'));
        $this->assertLazy($this->imgTagContaining($content, 'Panel Surya Uji Coba'));
    }

    public function test_produk_show_related_products_are_lazy(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $related = Product::factory()->create(['category_id' => $category->id, 'name' => 'Produk Terkait Unik', 'images' => ['products/related.webp']]);

        $content = $this->get('/produk/'.$product->slug)->assertOk()->getContent();

        $this->assertLazy($this->imgTagContaining($content, 'Produk Terkait Unik'));
    }

    public function test_artikel_index_featured_image_is_lazy(): void
    {
        Storage::fake('public');

        $article = Article::factory()->create([
            'title' => 'Artikel Unggulan Uji Coba',
            'image_path' => 'articles/featured.webp',
            'published_at' => now()->subDay(),
        ]);
        Storage::disk('public')->put('articles/featured.webp', 'fake-bytes');

        $content = $this->get('/artikel')->assertOk()->getContent();

        $this->assertLazy($this->imgTagContaining($content, 'Artikel Unggulan Uji Coba'));
    }

    public function test_artikel_show_cover_image_is_lazy(): void
    {
        Storage::fake('public');

        $article = Article::factory()->create([
            'title' => 'Artikel Detail Uji Coba',
            'image_path' => 'articles/cover.webp',
            'published_at' => now()->subDay(),
        ]);
        Storage::disk('public')->put('articles/cover.webp', 'fake-bytes');

        $content = $this->get('/artikel/'.$article->slug)->assertOk()->getContent();

        $this->assertLazy($this->imgTagContaining($content, 'Artikel Detail Uji Coba'));
    }

    public function test_portfolio_index_thumbnails_are_lazy(): void
    {
        $category = PortfolioCategory::factory()->create();
        $project = PortfolioProject::factory()->create([
            'portfolio_category_id' => $category->id,
            'title' => 'Proyek Portfolio Uji Coba',
            'is_active' => true,
        ]);

        $content = $this->get('/portfolio')->assertOk()->getContent();

        $this->assertLazy($this->imgTagContaining($content, 'Proyek Portfolio Uji Coba'));
    }

    public function test_portfolio_show_cover_is_eager_and_additional_thumbnails_are_lazy(): void
    {
        $category = PortfolioCategory::factory()->create();
        $project = PortfolioProject::factory()->create([
            'portfolio_category_id' => $category->id,
            'is_active' => true,
            'images' => ['portfolio/a.webp', 'portfolio/b.webp'],
        ]);

        $content = $this->get('/portfolio/'.$project->slug)->assertOk()->getContent();
        $tags = $this->allImgTags($content);

        $this->assertGreaterThanOrEqual(2, count($tags));
        $this->assertEager($tags[0]);
        $this->assertLazy($tags[1]);
    }

    public function test_tentang_kami_hero_is_eager_and_other_sections_are_lazy(): void
    {
        Storage::fake('public');

        $teamMember = TeamMember::factory()->create(['name' => 'Anggota Tim Unik', 'photo_path' => 'team/unik.webp']);
        Storage::disk('public')->put('team/unik.webp', 'fake-bytes');

        $clientLogo = ClientLogo::factory()->create(['company_name' => 'Klien Unik Sekali', 'logo_path' => 'client-logos/unik.webp']);
        Storage::disk('public')->put('client-logos/unik.webp', 'fake-bytes');

        $content = $this->get('/tentang-kami')->assertOk()->getContent();

        $this->assertEager($this->imgTagContaining($content, 'mockup/produk-1.jpg'));
        $this->assertLazy($this->imgTagContaining($content, 'Anggota Tim Unik'));
        $this->assertLazy($this->imgTagContaining($content, 'Klien Unik Sekali'));
    }
}
