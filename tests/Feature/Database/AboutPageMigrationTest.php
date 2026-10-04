<?php

namespace Tests\Feature\Database;

use App\Enums\PageBlockType;
use App\Enums\PageSection;
use App\Models\PageBlock;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use App\Settings\SiteSettings;
use App\Support\PageContent\PageContentInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AboutPageMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function resetPageContent(): void
    {
        SectionItem::query()->delete();
        SectionHeading::query()->delete();
        PageBlock::query()->delete();
    }

    private function setLegacy(string $name, mixed $value): void
    {
        DB::table('settings')->where('group', 'about_page')->where('name', $name)->update(['payload' => json_encode($value)]);
    }

    /**
     * @return list<string>
     */
    private function titles(PageSection $section): array
    {
        return SectionItem::query()->forSection($section)->ordered()->pluck('title')->all();
    }

    public function test_fresh_install_matches_legacy_default_settings(): void
    {
        $this->assertCount(5, $this->titles(PageSection::AboutMission));
        $this->assertSame(['Efisien & Terjangkau', 'Edukasi Masyarakat', 'Kolaborasi & Infrastruktur'], $this->titles(PageSection::AboutValues));
        $this->assertSame(['5.000+', '10+ MW', '15+ Tahun'], $this->titles(PageSection::AboutTrust));

        $trust = SectionItem::query()->forSection(PageSection::AboutTrust)->ordered()->first();
        $this->assertSame('Pelanggan Puas', $trust->description);
        $this->assertSame('group', $trust->icon);

        $mission = SectionHeading::query()->where('section', PageSection::AboutMission->value)->first();
        $this->assertSame('Bagaimana Kami Mewujudkannya', $mission->title);
        $this->assertSame('Misi', $mission->eyebrow);

        $values = SectionHeading::query()->where('section', PageSection::AboutValues->value)->first();
        $this->assertSame('eco', $values->featured_icon);
        $this->assertSame('Ekonomi Hijau & Lapangan Kerja', $values->featured_title);

        $hero = PageBlock::query()->where('block', PageBlockType::AboutHero->value)->first();
        $this->assertSame('Menghadirkan solusi energi surya inovatif dan berkelanjutan untuk masa depan Indonesia yang lebih cerah.', $hero->data['subtitle']);
        $this->assertNull($hero->data['image_path']);
    }

    public function test_admin_edited_legacy_values_are_moved_as_is(): void
    {
        $this->setLegacy('hero_image_path', 'about-page/hero-lama.webp');
        $this->setLegacy('hero_subtitle', 'Subjudul hasil editan admin.');
        $this->setLegacy('visi_heading', 'Visi hasil editan admin.');
        $this->setLegacy('misi_heading', 'Judul misi editan admin');
        $this->setLegacy('misi_items', json_encode([
            ['title' => 'Misi B', 'description' => 'Deskripsi B'],
            ['title' => 'Misi A', 'description' => 'Deskripsi A'],
        ]));
        $this->setLegacy('nilai_featured_image_path', 'about-page/kartu-lama.webp');
        $this->setLegacy('nilai_featured_title', 'Kartu besar editan');
        $this->setLegacy('trust_items', json_encode([
            ['icon' => 'group', 'value' => '9.999+', 'label' => 'Klien Editan'],
        ]));

        $this->resetPageContent();
        PageContentInstaller::install();

        $this->assertSame(['Misi B', 'Misi A'], $this->titles(PageSection::AboutMission));
        $this->assertSame('Judul misi editan admin', SectionHeading::query()->where('section', PageSection::AboutMission->value)->value('title'));
        $this->assertSame(['9.999+'], $this->titles(PageSection::AboutTrust));

        $values = SectionHeading::query()->where('section', PageSection::AboutValues->value)->first();
        $this->assertSame('about-page/kartu-lama.webp', $values->featured_image_path);
        $this->assertSame('Kartu besar editan', $values->featured_title);

        $hero = PageBlock::query()->where('block', PageBlockType::AboutHero->value)->first();
        $this->assertSame('about-page/hero-lama.webp', $hero->data['image_path']);
        $this->assertSame('Subjudul hasil editan admin.', $hero->data['subtitle']);
        $this->assertSame('Visi hasil editan admin.', PageBlock::query()->where('block', PageBlockType::AboutVision->value)->first()->data['heading']);
    }

    public function test_site_name_is_filled_once_into_who_we_are_body_and_contact_message(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Surya Jaya';
        $site->save();

        $this->resetPageContent();
        PageContentInstaller::install();

        $body = PageBlock::query()->where('block', PageBlockType::AboutWhoWeAre->value)->first()->data['body'];
        $this->assertStringContainsString('Surya Jaya hadir membawa', $body);
        $this->assertStringNotContainsString('{app_name}', $body);

        $message = PageBlock::query()->where('block', PageBlockType::ContactInfo->value)->first()->data['whatsapp_message'];
        $this->assertSame('Halo, saya ingin konsultasi tentang solusi tenaga surya Surya Jaya.', $message);
    }

    public function test_install_is_idempotent_and_keeps_legacy_settings_rows(): void
    {
        $before = DB::table('settings')->where('group', 'about_page')->count();

        PageContentInstaller::install();
        PageContentInstaller::install();

        $this->assertSame(5, SectionItem::query()->forSection(PageSection::AboutMission)->count());
        $this->assertSame(count(PageBlockType::cases()), PageBlock::query()->count());
        $this->assertSame($before, DB::table('settings')->where('group', 'about_page')->count());
        $this->assertGreaterThan(0, $before);
    }

    public function test_install_adds_new_hero_title_without_touching_existing_values(): void
    {
        PageBlock::query()->where('block', PageBlockType::AboutHero->value)->update(['data' => json_encode(['image_path' => 'about-page/hero.webp', 'subtitle' => null])]);

        PageContentInstaller::install();

        $data = PageBlock::query()->where('block', PageBlockType::AboutHero->value)->first()->data;
        $siteName = app(SiteSettings::class)->site_name ?: config('app.name');

        $this->assertSame("Mengenal {$siteName} Lebih Dekat", $data['title']);
        $this->assertSame('about-page/hero.webp', $data['image_path']);
        $this->assertNull($data['subtitle']);
    }

    public function test_install_never_overwrites_admin_edited_block(): void
    {
        PageBlock::query()->where('block', PageBlockType::AboutVision->value)->update(['data' => json_encode(['eyebrow' => 'Edit', 'heading' => 'Edit', 'subtext' => 'Edit'])]);

        PageContentInstaller::install();

        $this->assertSame('Edit', PageBlock::query()->where('block', PageBlockType::AboutVision->value)->first()->data['heading']);
    }
}
