<?php

namespace Tests\Feature\Public;

use App\Settings\ScriptSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Empat slot kode dimuat tepat pada posisi yang dijanjikan labelnya, apa
 * adanya sebagai kode (FR-041, FR-044, contracts §4).
 */
class ScriptSlotRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_head_script_appears_inside_head_tag(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->head_scripts = '<!-- PENANDA-HEAD-UNIK -->';
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $headEnd = strpos($content, '</head>');
        $markerPos = strpos($content, '<!-- PENANDA-HEAD-UNIK -->');

        $this->assertNotFalse($markerPos);
        $this->assertLessThan($headEnd, $markerPos);
    }

    public function test_body_start_script_appears_right_after_body_tag_opens(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->body_start_scripts = '<!-- PENANDA-BODY-START -->';
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $bodyTagStart = strpos($content, '<body');
        $bodyOpenEnd = strpos($content, '>', $bodyTagStart) + 1;
        // Header adalah elemen pertama yang benar-benar dirender di dalam
        // <body> (lihat x-layout.header) — penanda harus muncul sebelum itu.
        $headerTagStart = strpos($content, '<header');
        $markerPos = strpos($content, '<!-- PENANDA-BODY-START -->');

        $this->assertNotFalse($markerPos);
        $this->assertGreaterThanOrEqual($bodyOpenEnd, $markerPos);
        $this->assertLessThan($headerTagStart, $markerPos);
    }

    public function test_body_end_script_appears_right_before_body_tag_closes(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->body_end_scripts = '<!-- PENANDA-BODY-END -->';
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $bodyClose = strrpos($content, '</body>');
        $markerPos = strpos($content, '<!-- PENANDA-BODY-END -->');

        $this->assertNotFalse($markerPos);
        $this->assertLessThan($bodyClose, $markerPos);
        $this->assertGreaterThan($bodyClose - 500, $markerPos);
    }

    public function test_footer_script_appears_after_footer_component(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->footer_scripts = '<!-- PENANDA-FOOTER -->';
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $footerTagEnd = strrpos($content, '</footer>');
        $markerPos = strpos($content, '<!-- PENANDA-FOOTER -->');

        $this->assertNotFalse($markerPos);
        $this->assertGreaterThan($footerTagEnd, $markerPos);
    }

    public function test_script_content_is_rendered_unescaped(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->head_scripts = '<script>window.dataLayer = window.dataLayer || [];</script>';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<script>window.dataLayer = window.dataLayer || [];</script>', escape: false);
        $response->assertDontSee('&lt;script&gt;', escape: false);
    }
}
