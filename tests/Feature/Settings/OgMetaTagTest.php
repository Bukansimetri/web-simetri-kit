<?php

namespace Tests\Feature\Settings;

use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OgMetaTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_uses_default_og_image_when_unconfigured(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('og:image" content="'.asset(BrandSettings::DEFAULT_OG_IMAGE_PATH).'"', escape: false);
    }

    public function test_public_page_uses_uploaded_og_image_when_configured(): void
    {
        $settings = app(BrandSettings::class);
        $settings->og_image_path = 'branding/custom-og.jpg';
        $settings->save();

        app()->forgetInstance(BrandSettings::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee(BrandSettings::DEFAULT_OG_IMAGE_PATH, escape: false);
    }
}
