<?php

namespace Tests\Feature\Database;

use App\Filament\Resources\JobOpeningResource\Pages\EditJobOpening;
use App\Models\JobOpening;
use App\Models\User;
use App\Settings\SiteSettings;
use App\Support\PageContent\JobDescriptionConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobDescriptionConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_lines_become_paragraphs_and_single_newlines_become_breaks(): void
    {
        $this->assertSame(
            '<p>Baris satu<br>Baris dua</p><p>Paragraf kedua</p>',
            JobDescriptionConverter::toHtml("Baris satu\nBaris dua\n\nParagraf kedua"),
        );
    }

    public function test_windows_newlines_single_line_and_multiple_blank_lines(): void
    {
        $this->assertSame('<p>A<br>B</p><p>C</p>', JobDescriptionConverter::toHtml("A\r\nB\r\n\r\n\r\n\r\nC"));
        $this->assertSame('<p>Satu baris saja.</p>', JobDescriptionConverter::toHtml('Satu baris saja.'));
        $this->assertSame('', JobDescriptionConverter::toHtml("  \n "));
    }

    public function test_special_characters_are_escaped(): void
    {
        $this->assertSame('<p>3 &lt; 5 &amp; 7 &gt; 2</p>', JobDescriptionConverter::toHtml('3 < 5 & 7 > 2'));
    }

    public function test_existing_html_is_left_untouched_and_conversion_is_idempotent(): void
    {
        $html = '<h2>Judul</h2><ul><li>Satu</li></ul>';
        $once = JobDescriptionConverter::toHtml("Teks lama\n\nParagraf");

        $this->assertSame($html, JobDescriptionConverter::toHtml($html));
        $this->assertSame($once, JobDescriptionConverter::toHtml($once));
    }

    public function test_migration_converts_stored_plain_descriptions(): void
    {
        $plain = JobOpening::factory()->create(['description' => "Tugas A\nTugas B\n\nSyarat <wajib> & penting"]);
        $html = JobOpening::factory()->create(['description' => '<p>Sudah HTML.</p>']);

        $migration = require database_path('migrations/2026_10_05_214746_convert_job_opening_descriptions_to_html.php');
        $migration->up();
        $migration->up();

        $this->assertSame('<p>Tugas A<br>Tugas B</p><p>Syarat &lt;wajib&gt; &amp; penting</p>', $plain->fresh()->description);
        $this->assertSame('<p>Sudah HTML.</p>', $html->fresh()->description);
    }

    public function test_converted_description_renders_publicly_with_special_characters_as_text(): void
    {
        $site = app(SiteSettings::class);
        $site->career_module_enabled = true;
        $site->save();

        $job = JobOpening::factory()->create([
            'description' => JobDescriptionConverter::toHtml("Baris satu\nBaris dua\n\nGaji > 5 & bonus <10"),
            'is_active' => true,
        ]);

        $this->get(route('karir.show', $job))
            ->assertOk()
            ->assertSee('<p>Baris satu<br>Baris dua</p>', escape: false)
            ->assertSee('Gaji &gt; 5 &amp; bonus &lt;10', escape: false);
    }

    public function test_converted_description_opens_intact_in_editor_and_can_be_saved_again(): void
    {
        $converted = JobDescriptionConverter::toHtml("Baris satu\nBaris dua\n\nParagraf kedua");
        $job = JobOpening::factory()->create(['description' => $converted]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditJobOpening::class, ['record' => $job->getRouteKey()])
            ->assertFormSet(['description' => $converted])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertStringContainsString('Baris satu', $job->fresh()->description);
        $this->assertStringContainsString('Paragraf kedua', $job->fresh()->description);
    }
}
