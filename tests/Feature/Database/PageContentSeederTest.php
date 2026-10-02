<?php

namespace Tests\Feature\Database;

use App\Enums\CtaPlacement;
use App\Enums\PageSection;
use App\Models\CallToAction;
use App\Models\PageBlock;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use App\Settings\SiteSettings;
use App\Support\PageContent\PageContentInstaller;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_install_default_content(): void
    {
        $this->assertSame(3, SectionItem::query()->forSection(PageSection::WhyChoose)->count());
        $this->assertSame(4, SectionItem::query()->forSection(PageSection::HowItWorks)->count());
        $this->assertSame(3, SectionItem::query()->forSection(PageSection::CareerValues)->count());
        $this->assertSame(4, SectionItem::query()->forSection(PageSection::RecruitmentProcess)->count());
        $this->assertSame(5, SectionItem::query()->forSection(PageSection::AboutMission)->count());
        $this->assertSame(3, SectionItem::query()->forSection(PageSection::AboutValues)->count());
        $this->assertSame(3, SectionItem::query()->forSection(PageSection::AboutTrust)->count());
        $this->assertSame(7, SectionHeading::query()->count());
        $this->assertSame(4, PageBlock::query()->count());
        $this->assertSame(9, CallToAction::query()->count());
    }

    public function test_installer_and_seeder_are_idempotent(): void
    {
        PageContentInstaller::install();
        PageContentInstaller::install();
        $this->seed(PageContentSeeder::class);

        $this->assertSame(25, SectionItem::query()->count());
        $this->assertSame(7, SectionHeading::query()->count());
        $this->assertSame(4, PageBlock::query()->count());
        $this->assertSame(9, CallToAction::query()->count());
    }

    public function test_installer_never_overwrites_admin_edits(): void
    {
        SectionHeading::query()->where('section', PageSection::WhyChoose->value)->update(['title' => 'Judul Admin']);
        CallToAction::query()->where('placement', CtaPlacement::Faq->value)->update(['title' => 'CTA Admin']);

        PageContentInstaller::install();

        $this->assertSame('Judul Admin', SectionHeading::query()->where('section', PageSection::WhyChoose->value)->value('title'));
        $this->assertSame('CTA Admin', CallToAction::query()->where('placement', CtaPlacement::Faq->value)->value('title'));
    }

    public function test_section_whose_items_were_all_deleted_is_not_reseeded(): void
    {
        SectionItem::query()->forSection(PageSection::RecruitmentProcess)->delete();

        PageContentInstaller::install();

        $this->assertSame(0, SectionItem::query()->forSection(PageSection::RecruitmentProcess)->count());
    }

    public function test_installer_fills_site_name_once_into_stored_text(): void
    {
        CallToAction::query()->delete();
        SectionItem::query()->delete();
        SectionHeading::query()->delete();

        $site = app(SiteSettings::class);
        $site->site_name = 'Surya Jaya';
        $site->save();

        PageContentInstaller::install();

        $this->assertSame("Mengapa Beralih\nBersama Surya Jaya?", SectionHeading::query()->where('section', PageSection::WhyChoose->value)->value('title'));
        $this->assertSame('Ingin tahu lebih lanjut tentang Surya Jaya?', CallToAction::query()->where('placement', CtaPlacement::About->value)->value('title'));
        $this->assertSame('Konsultasi gratis dengan tim Surya Jaya', CallToAction::query()->where('placement', CtaPlacement::ProductClosing->value)->value('primary_label'));
        $this->assertSame(0, CallToAction::query()->where('title', 'like', '%{app_name}%')->orWhere('primary_label', 'like', '%{app_name}%')->count());
    }

    public function test_data_migration_does_not_run_seeders(): void
    {
        $migrations = glob(database_path('migrations/*_install_default_page_content.php'));

        $this->assertCount(1, $migrations);
        $this->assertStringNotContainsString('Database\\Seeders', file_get_contents($migrations[0]));
    }
}
