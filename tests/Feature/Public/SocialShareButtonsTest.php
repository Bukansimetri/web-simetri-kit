<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\Product;
use App\Settings\SocialSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tombol berbagi tampil sesuai platform pilihan admin pada halaman artikel
 * dan produk, membawa alamat konten yang sedang dibuka (FR-022, FR-023,
 * contracts §9).
 */
class SocialShareButtonsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_selected_platforms_render_on_article(): void
    {
        $settings = app(SocialSettings::class);
        $settings->share_buttons_enabled = true;
        $settings->share_platforms = ['facebook', 'whatsapp'];
        $settings->save();

        $article = Article::factory()->create(['published_at' => now()->subDay()]);

        $response = $this->get('/artikel/'.$article->slug);

        $response->assertOk();
        $response->assertSee('aria-label="Bagikan ke Facebook"', escape: false);
        $response->assertSee('aria-label="Bagikan ke WhatsApp"', escape: false);
        $response->assertDontSee('aria-label="Bagikan ke Twitter/X"', escape: false);
        $response->assertDontSee('aria-label="Bagikan ke LinkedIn"', escape: false);
    }

    public function test_share_link_carries_current_content_url(): void
    {
        $settings = app(SocialSettings::class);
        $settings->share_buttons_enabled = true;
        $settings->share_platforms = ['facebook'];
        $settings->save();

        $product = Product::factory()->create();

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee(urlencode(url('/produk/'.$product->slug)), escape: false);
    }

    public function test_no_buttons_rendered_when_disabled(): void
    {
        $article = Article::factory()->create(['published_at' => now()->subDay()]);

        $response = $this->get('/artikel/'.$article->slug);

        $response->assertOk();
        $response->assertDontSee('aria-label="Bagikan ke', escape: false);
    }
}
