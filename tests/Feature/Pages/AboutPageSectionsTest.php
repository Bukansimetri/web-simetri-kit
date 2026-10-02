<?php

namespace Tests\Feature\Pages;

use App\Enums\PageSection;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class AboutPageSectionsTest extends TestCase
{
    use RefreshDatabase;

    private function fragment(string $name): string
    {
        return LegacyMarkup::extract($this->get('/tentang-kami')->assertOk()->getContent(), LegacyMarkup::FRAGMENTS[$name][1]);
    }

    public function test_mission_splits_three_left_and_rest_right_by_position(): void
    {
        SectionItem::query()->where('title', 'Inovasi Teknologi')->update(['order' => -1]);

        $fragment = $this->fragment('tentang-kami-misi');

        [$left, $right] = explode('md:col-span-2', $fragment, 2);

        $this->assertStringContainsString('Inovasi Teknologi', $left);
        $this->assertStringContainsString('Solusi Premium', $left);
        $this->assertStringNotContainsString('Dukungan Purna Jual', $left);
        $this->assertStringContainsString('Dukungan Purna Jual', $right);
        $this->assertStringContainsString('Edukasi Berkelanjutan', $right);
    }

    public function test_mission_heading_eyebrow_title_and_subtitle_are_editable(): void
    {
        SectionHeading::query()->where('section', PageSection::AboutMission->value)->update([
            'eyebrow' => 'Misi Baru',
            'title' => 'Judul Misi Baru',
            'subtitle' => 'Subjudul misi baru.',
        ]);

        $fragment = $this->fragment('tentang-kami-misi');

        $this->assertStringContainsString('Misi Baru', $fragment);
        $this->assertStringContainsString('Judul Misi Baru', $fragment);
        $this->assertStringContainsString('Subjudul misi baru.', $fragment);
    }

    public function test_values_featured_card_comes_from_heading_and_uses_default_image_when_empty(): void
    {
        SectionHeading::query()->where('section', PageSection::AboutValues->value)->update([
            'featured_title' => 'Kartu Besar Baru',
            'featured_icon' => 'bolt',
            'featured_image_path' => null,
        ]);

        $fragment = $this->fragment('tentang-kami-nilai');

        $this->assertStringContainsString('Kartu Besar Baru', $fragment);
        $this->assertStringContainsString('>bolt<', $fragment);
        $this->assertStringContainsString('images/mockup/tentang-kami-3.jpg', $fragment);
    }

    public function test_values_cards_follow_admin_order(): void
    {
        SectionItem::query()->where('title', 'Kolaborasi & Infrastruktur')->update(['order' => -1]);

        $this->get('/tentang-kami')->assertSeeInOrder(['Kolaborasi &amp; Infrastruktur', 'Efisien &amp; Terjangkau', 'Edukasi Masyarakat'], escape: false);
    }

    public function test_trust_strip_shows_numbers_and_labels(): void
    {
        $fragment = $this->fragment('tentang-kami-trust');

        foreach (['5.000+', 'Pelanggan Puas', '10+ MW', 'Total Kapasitas Terpasang', '15+ Tahun', 'Pengalaman Industri'] as $text) {
            $this->assertStringContainsString($text, $fragment);
        }
    }

    public function test_sections_are_hidden_when_no_active_items(): void
    {
        SectionItem::query()->forSection(PageSection::AboutTrust)->update(['is_active' => false]);
        SectionItem::query()->forSection(PageSection::AboutMission)->update(['is_active' => false]);

        $this->get('/tentang-kami')->assertOk()
            ->assertDontSee('Pelanggan Puas', escape: false)
            ->assertDontSee('Bagaimana Kami Mewujudkannya', escape: false)
            ->assertSee('Nilai-Nilai Kami', escape: false);
    }
}
