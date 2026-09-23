<?php

namespace Tests\Feature\Public;

use App\Settings\ScriptSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Slot berkategori analitik/pemasaran dirender dalam bentuk yang tidak
 * dieksekusi peramban sampai disetujui; slot berkategori none tetap
 * dirender sebagai kode biasa (FR-054, contracts/consent-gating-contract.md
 * §5).
 */
class ConsentGatedScriptTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_slot_is_wrapped_in_inert_template(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->head_scripts = '<script>window.gaPenanda = true;</script>';
        $settings->head_scripts_consent = ScriptSettings::CONSENT_ANALYTICS;
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-consent-category="analytics"', $content);
        $this->assertStringContainsString('<template', $content);

        // Bukan dieksekusi langsung: penanda skrip harus berada di DALAM
        // elemen <template>, bukan sebagai <script> biasa yang berjalan
        // segera saat halaman dimuat.
        $templateStart = strpos($content, '<template');
        $templateEnd = strpos($content, '</template>');
        $markerPos = strpos($content, 'window.gaPenanda = true;');

        $this->assertGreaterThan($templateStart, $markerPos);
        $this->assertLessThan($templateEnd, $markerPos);
    }

    public function test_none_category_slot_is_not_wrapped_in_template(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->footer_scripts = '<!-- PENANDA-TANPA-KATEGORI -->';
        $settings->footer_scripts_consent = ScriptSettings::CONSENT_NONE;
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $markerPos = strpos($content, '<!-- PENANDA-TANPA-KATEGORI -->');
        $this->assertNotFalse($markerPos);

        // Tidak ada <template> yang membungkus penanda ini.
        $beforeMarker = substr($content, 0, $markerPos);
        $lastTemplateOpen = strrpos($beforeMarker, '<template');
        $lastTemplateClose = strrpos($beforeMarker, '</template>');

        $this->assertTrue($lastTemplateOpen === false || $lastTemplateClose > $lastTemplateOpen);
    }
}
