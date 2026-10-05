<?php

namespace Tests\Feature\Public;

use App\Models\JobOpening;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOpeningRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $site = app(SiteSettings::class);
        $site->career_module_enabled = true;
        $site->save();
    }

    private function job(string $description, string $title = 'Teknisi Uji'): JobOpening
    {
        return JobOpening::factory()->create([
            'title' => $title,
            'description' => $description,
            'is_active' => true,
        ]);
    }

    public function test_detail_renders_formatted_description(): void
    {
        $job = $this->job('<h2>Kualifikasi</h2><ul><li>Berpengalaman</li></ul><p><strong>Tebal</strong> <a href="https://contoh.test">tautan</a></p><blockquote>Kutipan</blockquote>');

        $this->get(route('karir.show', $job))
            ->assertOk()
            ->assertSee('<h2>Kualifikasi</h2>', escape: false)
            ->assertSee('<li>Berpengalaman</li>', escape: false)
            ->assertSee('<strong>Tebal</strong>', escape: false)
            ->assertSee('href="https://contoh.test"', escape: false)
            ->assertSee('<blockquote>Kutipan</blockquote>', escape: false)
            ->assertSee('[&_ul]:list-disc', escape: false)
            ->assertSee('Lamar Sekarang')
            ->assertSee('Kembali ke Karir');
    }

    public function test_description_changes_are_visible_immediately(): void
    {
        $job = $this->job('<p>Isi awal.</p>');
        $this->get(route('karir.show', $job))->assertSee('Isi awal.');
        $this->get(route('karir'))->assertSee('Isi awal.');

        $job->update(['description' => '<p>Isi baru.</p>']);

        $this->get(route('karir.show', $job))->assertSee('Isi baru.')->assertDontSee('Isi awal.');
        $this->get(route('karir'))->assertSee('Isi baru.')->assertDontSee('Isi awal.');
    }

    public function test_excerpt_separates_blocks_and_strips_tags(): void
    {
        $job = $this->job('<h2>Tugas</h2><p>Pasang panel &amp; rawat.</p><ul><li>Satu</li><li>Dua</li></ul>');

        $this->assertSame('Tugas Pasang panel & rawat. Satu Dua', $job->descriptionExcerpt());
        $this->assertSame('Tugas Pasang pa...', $job->descriptionExcerpt(15));
    }

    public function test_career_card_shows_plain_excerpt_without_format_tags(): void
    {
        $this->job('<h2>Tugas Utama</h2><ul><li>Pasang panel</li></ul><p><strong>Penting</strong></p>');

        $response = $this->get(route('karir'))->assertOk();

        $response->assertSee('Tugas Utama Pasang panel Penting')
            ->assertDontSee('<h2>Tugas Utama</h2>', escape: false)
            ->assertDontSee('<strong>Penting</strong>', escape: false)
            ->assertSee('line-clamp-2', escape: false);
    }

    public function test_meta_description_is_plain_and_limited(): void
    {
        $job = $this->job('<h2>Tugas</h2><p>'.str_repeat('Kata panjang. ', 40).'</p>');

        $html = $this->get(route('karir.show', $job))->assertOk()->getContent();

        preg_match('/<meta name="description" content="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m[1]);
        $this->assertLessThanOrEqual(158, mb_strlen(html_entity_decode($m[1])));
        $this->assertStringNotContainsString('&lt;', $m[1]);
        $this->assertStringStartsWith('Tugas Kata panjang.', html_entity_decode($m[1]));
    }

    public function test_dangerous_markup_never_reaches_the_page(): void
    {
        $job = $this->job('<p>Aman</p><script>alert(1)</script><img src="x" onerror="alert(2)"><p onclick="alert(3)">Teks</p><a href="javascript:alert(4)">klik</a><iframe src="https://x.test"></iframe><style>body{display:none}</style>');

        $html = $this->get(route('karir.show', $job))->assertOk()->getContent();
        $this->get(route('karir'))->assertOk()->assertDontSee('<script>alert', escape: false);

        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('<style>body', $html);
        $this->assertStringContainsString('Aman', $html);
    }
}
