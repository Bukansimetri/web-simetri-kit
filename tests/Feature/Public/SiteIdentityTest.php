<?php

namespace Tests\Feature\Public;

use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Atribut bahasa dokumen dan nama situs memakai pengaturan (FR-001, FR-005,
 * contracts §1).
 */
class SiteIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_language_attribute_follows_default_language_setting(): void
    {
        $settings = app(SiteSettings::class);
        $settings->default_language = 'en';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<html lang="en"', escape: false);
    }

    public function test_document_language_defaults_to_indonesian(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<html lang="id"', escape: false);
    }

    public function test_site_name_is_used_in_title_and_og_site_name(): void
    {
        $settings = app(SiteSettings::class);
        $settings->site_name = 'Nama Situs Uji';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<title>Nama Situs Uji</title>', escape: false);
        $response->assertSee('og:site_name" content="Nama Situs Uji"', escape: false);
    }
}
