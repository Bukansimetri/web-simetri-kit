<?php

namespace Tests\Feature\Pages;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeBannerTest extends TestCase
{
    use RefreshDatabase;

    private const HERO_HEADLINE = 'Nyalakan rumah Anda dengan energi';

    protected function makeBanner(array $attributes = []): Banner
    {
        $banner = Banner::factory()->create($attributes);
        Storage::disk('public')->put($banner->image_path, 'fake-bytes');

        return $banner;
    }

    protected function persistFile(Banner $banner): Banner
    {
        Storage::disk('public')->put($banner->image_path, 'fake-bytes');

        return $banner;
    }

    public function test_live_banners_render_in_hero_slot_instead_of_static_hero(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Kedua', 'order' => 2]);
        $this->makeBanner(['alt_text' => 'Banner Pertama', 'order' => 1]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder(['Banner Pertama', 'Banner Kedua'], escape: false);
        $response->assertDontSee(self::HERO_HEADLINE, escape: false);
    }

    public function test_banner_with_link_is_wrapped_in_anchor(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Promo', 'link_url' => 'https://x.test/promo']);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://x.test/promo"', escape: false);
    }

    public function test_scheduled_expired_and_inactive_banners_do_not_appear(): void
    {
        Storage::fake('public');
        $this->persistFile(Banner::factory()->scheduled()->create(['alt_text' => 'Terjadwal']));
        $this->persistFile(Banner::factory()->expired()->create(['alt_text' => 'Kedaluwarsa']));
        $this->persistFile(Banner::factory()->create(['alt_text' => 'Nonaktif', 'is_active' => false]));

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Terjadwal', escape: false);
        $response->assertDontSee('Kedaluwarsa', escape: false);
        $response->assertDontSee('Nonaktif', escape: false);
        $response->assertSee(self::HERO_HEADLINE, escape: false);
    }

    public function test_static_hero_is_shown_when_no_live_banner(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(self::HERO_HEADLINE, escape: false);
    }

    public function test_other_pages_do_not_show_banner(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Beranda Saja']);

        $this->get('/produk')
            ->assertOk()
            ->assertDontSee('Banner Beranda Saja', escape: false);
    }
}
