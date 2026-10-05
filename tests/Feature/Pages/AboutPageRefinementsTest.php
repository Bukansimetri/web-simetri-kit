<?php

namespace Tests\Feature\Pages;

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

/**
 * Perbaikan halaman Tentang Kami dari dokumen klien (spec 031 US7).
 */
class AboutPageRefinementsTest extends TestCase
{
    use RefreshDatabase;

    private function setWhoWeAre(array $data): void
    {
        $block = PageBlock::query()->where('block', PageBlockType::AboutWhoWeAre->value)->firstOrFail();
        $block->update(['data' => [...$block->data, ...$data]]);
    }

    private function whoWeAreSection(): string
    {
        return LegacyMarkup::extract($this->get('/tentang-kami')->assertOk()->getContent(), "//section[.//div[contains(@class, '-rotate-3')]]");
    }

    public function test_filled_quote_is_shown_with_its_bar(): void
    {
        $this->setWhoWeAre(['quote' => 'Misi kami bukan sekadar menjual panel.']);

        $section = $this->whoWeAreSection();

        $this->assertStringContainsString('border-l-4', $section);
        $this->assertStringContainsString('Misi kami bukan sekadar menjual panel.', $section);
    }

    /**
     * @return array<string, array{0: ?string}>
     */
    public static function emptyQuotes(): array
    {
        return [
            'null' => [null],
            'string kosong' => [''],
            'spasi' => ['   '],
            'paragraf kosong' => ['<p></p>'],
            'paragraf spasi' => ['<p> &nbsp; </p>'],
        ];
    }

    #[DataProvider('emptyQuotes')]
    public function test_empty_quote_hides_the_bar_and_the_quote_marks(?string $quote): void
    {
        $this->setWhoWeAre(['quote' => $quote]);

        $section = $this->whoWeAreSection();

        $this->assertStringNotContainsString('border-l-4', $section);
        $this->assertStringNotContainsString('&ldquo;', $section);
        $this->assertStringNotContainsString('“', $section);
    }

    public function test_testimonial_section_uses_partner_kami_title(): void
    {
        Testimonial::factory()->create();

        $this->get('/tentang-kami')->assertOk()
            ->assertSee('Testimoni')
            ->assertSee('Partner Kami')
            ->assertDontSee('Apa Kata Klien Kami');
    }

    public function test_team_section_is_full_width_with_a_contained_inner_wrapper(): void
    {
        Storage::fake('public');
        $member = TeamMember::factory()->create(['name' => 'Mr John']);
        Storage::disk('public')->put($member->photo_path, 'x');

        $html = $this->get('/tentang-kami')->assertOk()->getContent();
        $section = LegacyMarkup::extract($html, "//section[.//span[contains(., 'Tim Kami')]]");

        $this->assertStringStartsWith('<section class="w-full bg-background', $section);
        $this->assertStringNotContainsString('<section class="py-24 px-6 max-w-7xl', $section);
        $this->assertStringContainsString('<div class="max-w-7xl mx-auto">', $section);
        $this->assertStringContainsString('flex flex-wrap justify-center', $section);
    }

    public function test_single_team_member_is_centered_and_rendered_completely(): void
    {
        Storage::fake('public');
        $member = TeamMember::factory()->create(['name' => 'Satu Anggota', 'position' => 'Engineer', 'bio' => 'Saya adalah Engineer']);
        Storage::disk('public')->put($member->photo_path, 'x');

        $html = $this->get('/tentang-kami')->assertOk()->getContent();
        $section = LegacyMarkup::extract($html, "//section[.//span[contains(., 'Tim Kami')]]");

        $this->assertSame(1, substr_count($section, 'md:w-56'));
        $this->assertStringContainsString('Satu Anggota', $section);
        $this->assertStringContainsString('Engineer', $section);
        $this->assertStringNotContainsString('grid-cols', $section);
    }

    public function test_team_section_is_hidden_without_active_members(): void
    {
        $this->get('/tentang-kami')->assertOk()->assertDontSee('Orang di balik');
    }
}
