<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_allowed_tags_pass_through(): void
    {
        $html = '<p>Halo <strong>dunia</strong></p><ul><li>Satu</li></ul>';

        $this->assertSame($html, HtmlSanitizer::clean($html));
    }

    public function test_disallowed_tags_are_stripped_but_text_survives(): void
    {
        $clean = HtmlSanitizer::clean('<script>alert(1)</script><p>Aman</p><iframe src="x"></iframe>');

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringContainsString('<p>Aman</p>', $clean);
    }

    public function test_on_attributes_are_stripped(): void
    {
        $clean = HtmlSanitizer::clean('<img src="/a.webp" onerror="alert(1)" alt="x">');

        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('src="/a.webp"', $clean);
    }

    public function test_javascript_scheme_is_rejected(): void
    {
        $clean = HtmlSanitizer::clean('<a href="javascript:alert(1)">Klik</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_null_input_returns_empty_string(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
    }

    public function test_blank_input_returns_empty_string(): void
    {
        $this->assertSame('', HtmlSanitizer::clean('   '));
    }

    public function test_allowed_url_schemes_are_kept(): void
    {
        $clean = HtmlSanitizer::clean('<a href="https://x.test">L</a><a href="mailto:a@x.test">M</a><a href="tel:0800">T</a><a href="/relatif">R</a>');

        $this->assertStringContainsString('href="https://x.test"', $clean);
        $this->assertStringContainsString('href="mailto:a@x.test"', $clean);
        $this->assertStringContainsString('href="tel:0800"', $clean);
        $this->assertStringContainsString('href="/relatif"', $clean);
    }

    public function test_headings_and_blockquote_are_allowed(): void
    {
        $html = '<h2>Kualifikasi</h2><h3>Wajib</h3><h4>Nilai Plus</h4><blockquote>Kutipan</blockquote>';

        $this->assertSame($html, HtmlSanitizer::clean($html));
    }

    public function test_heading_attributes_are_still_sanitized(): void
    {
        $clean = HtmlSanitizer::clean('<h2 onclick="x()">Judul</h2>');

        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringContainsString('<h2>Judul</h2>', $clean);
    }

    public function test_other_headings_and_pre_are_unwrapped(): void
    {
        $clean = HtmlSanitizer::clean('<h1>Besar</h1><pre>kode</pre>');

        $this->assertStringNotContainsString('<h1', $clean);
        $this->assertStringNotContainsString('<pre', $clean);
        $this->assertStringContainsString('Besar', $clean);
    }
}
