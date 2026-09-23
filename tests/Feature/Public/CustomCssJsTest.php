<?php

namespace Tests\Feature\Public;

use App\Settings\ScriptSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CSS khusus dimuat di kepala dokumen, JS khusus dimuat sebelum badan
 * ditutup (FR-042, FR-043, contracts §4).
 */
class CustomCssJsTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_css_is_loaded_in_head_as_style_block(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->custom_css = '.penanda-css-unik { color: red; }';
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $headEnd = strpos($content, '</head>');
        $markerPos = strpos($content, '.penanda-css-unik { color: red; }');

        $this->assertNotFalse($markerPos);
        $this->assertLessThan($headEnd, $markerPos);
    }

    public function test_custom_js_is_loaded_before_body_closes(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->custom_js = 'console.log("penanda-js-unik");';
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $bodyClose = strrpos($content, '</body>');
        $markerPos = strpos($content, 'console.log("penanda-js-unik");');

        $this->assertNotFalse($markerPos);
        $this->assertLessThan($bodyClose, $markerPos);
    }
}
