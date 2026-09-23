<?php

namespace Tests\Feature\Settings;

use App\Settings\SocialSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OgMetaTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_uses_default_og_image_when_unconfigured(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('og:image" content="'.asset(SocialSettings::DEFAULT_SHARE_IMAGE_PATH).'"', escape: false);
    }

    public function test_public_page_uses_uploaded_og_image_when_configured(): void
    {
        $settings = app(SocialSettings::class);
        $settings->default_share_image_path = 'branding/custom-og.jpg';
        $settings->save();

        app()->forgetInstance(SocialSettings::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee(SocialSettings::DEFAULT_SHARE_IMAGE_PATH, escape: false);
    }
}
