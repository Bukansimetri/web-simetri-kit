<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\Category;
use App\Models\CustomPage;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_valid_xml_with_correct_content_type(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', escape: false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', escape: false);
    }

    public function test_sitemap_always_contains_the_seven_static_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        foreach (['/', '/tentang-kami', '/kontak', '/faq', '/artikel', '/produk', '/portfolio'] as $uri) {
            $response->assertSee('<loc>'.url($uri).'</loc>', escape: false);
        }
    }

    public function test_sitemap_includes_karir_only_when_module_enabled(): void
    {
        $settings = app(BrandSettings::class);
        $settings->career_module_enabled = true;
        $settings->save();

        $this->get('/sitemap.xml')->assertSee('<loc>'.url('/karir').'</loc>', escape: false);

        $settings->career_module_enabled = false;
        $settings->save();

        $this->get('/sitemap.xml')->assertDontSee(url('/karir'), escape: false);
    }

    public function test_sitemap_includes_every_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.url('/produk/'.$product->slug).'</loc>', escape: false);
    }

    public function test_sitemap_includes_published_articles_but_not_draft_or_scheduled(): void
    {
        $published = Article::factory()->create(['published_at' => now()->subDay()]);
        $draft = Article::factory()->create(['published_at' => null]);
        $scheduled = Article::factory()->create(['published_at' => now()->addDay()]);

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.url('/artikel/'.$published->slug).'</loc>', escape: false);
        $response->assertDontSee(url('/artikel/'.$draft->slug), escape: false);
        $response->assertDontSee(url('/artikel/'.$scheduled->slug), escape: false);
    }

    public function test_sitemap_includes_every_custom_page(): void
    {
        $page = CustomPage::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.url('/halaman/'.$page->slug).'</loc>', escape: false);
    }

    public function test_sitemap_includes_active_portfolio_projects_but_not_inactive(): void
    {
        $category = PortfolioCategory::factory()->create();
        $active = PortfolioProject::factory()->create(['portfolio_category_id' => $category->id, 'is_active' => true]);
        $inactive = PortfolioProject::factory()->create(['portfolio_category_id' => $category->id, 'is_active' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.url('/portfolio/'.$active->slug).'</loc>', escape: false);
        $response->assertDontSee(url('/portfolio/'.$inactive->slug), escape: false);
    }

    public function test_every_url_in_sitemap_is_reachable(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);
        Article::factory()->create(['published_at' => now()->subDay()]);
        CustomPage::factory()->create();
        $portfolioCategory = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['portfolio_category_id' => $portfolioCategory->id, 'is_active' => true]);

        $xml = $this->get('/sitemap.xml')->getContent();
        preg_match_all('/<loc>(.*?)<\/loc>/', $xml, $matches);
        $locations = $matches[1];

        $this->assertNotEmpty($locations);

        foreach ($locations as $location) {
            $path = parse_url($location, PHP_URL_PATH) ?: '/';
            $this->get($path)->assertOk();
        }
    }

    public function test_sitemap_is_ok_when_there_is_no_content_at_all(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('<loc>'.url('/').'</loc>', escape: false);
    }
}
