<?php

namespace Tests\Feature\Public;

use App\Settings\SocialSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoGlobalMetaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private const PUBLIC_ROUTES = ['/', '/kontak', '/karir', '/faq', '/tentang-kami'];

    public function test_every_public_page_has_complete_og_and_twitter_meta(): void
    {
        foreach (self::PUBLIC_ROUTES as $uri) {
            $response = $this->get($uri);

            $response->assertOk();
            $response->assertSee('property="og:type"', escape: false);
            $response->assertSee('property="og:site_name"', escape: false);
            $response->assertSee('property="og:title"', escape: false);
            $response->assertSee('property="og:description"', escape: false);
            $response->assertSee('property="og:image"', escape: false);
            $response->assertSee('name="twitter:card" content="summary_large_image"', escape: false);
            $response->assertSee('name="twitter:title"', escape: false);
            $response->assertSee('name="twitter:description"', escape: false);
            $response->assertSee('name="twitter:image"', escape: false);
        }
    }

    public function test_every_public_page_has_canonical_link_matching_its_own_url(): void
    {
        foreach (self::PUBLIC_ROUTES as $uri) {
            $response = $this->get($uri);

            $response->assertOk();
            $response->assertSee('rel="canonical" href="'.url($uri).'"', escape: false);
        }
    }

    public function test_every_public_page_has_organization_json_ld(): void
    {
        foreach (self::PUBLIC_ROUTES as $uri) {
            $response = $this->get($uri);

            $response->assertOk();
            $response->assertSee('"@type":"Organization"', escape: false);
        }
    }

    /**
     * Tidak ada halaman publik yang saat ini meng-override `og_image` per
     * halaman (itu baru datang di US2/US3 untuk halaman detail konten) —
     * jadi setiap halaman statis di atas MASIH memakai fallback situs,
     * menjadikannya kasus nyata untuk menguji FR-003/FR-012 (bukan halaman
     * buatan): ubah setting → langsung berlaku; kosongkan → default bawaan.
     */
    public function test_custom_site_wide_og_image_is_used_across_pages_without_override(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/custom-og.jpg', 'fake-bytes');

        $settings = app(SocialSettings::class);
        $settings->default_share_image_path = 'branding/custom-og.jpg';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(Storage::disk('public')->url('branding/custom-og.jpg'), escape: false);
    }

    public function test_default_og_image_is_used_when_no_setting_configured(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(asset(SocialSettings::DEFAULT_SHARE_IMAGE_PATH), escape: false);
    }
}
