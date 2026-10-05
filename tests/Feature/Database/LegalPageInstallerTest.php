<?php

namespace Tests\Feature\Database;

use App\Enums\CustomPageTemplate;
use App\Models\CustomPage;
use App\Settings\SiteSettings;
use App\Support\MaterialSymbolsIcons;
use App\Support\PageContent\LegalPageInstaller;
use Database\Seeders\LegalPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPageInstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_install_both_legal_pages(): void
    {
        $privacy = CustomPage::query()->where('slug', 'kebijakan-privasi')->firstOrFail();
        $terms = CustomPage::query()->where('slug', 'syarat-ketentuan')->firstOrFail();

        $this->assertSame(CustomPageTemplate::Legal, $privacy->template);
        $this->assertCount(7, $privacy->legal['sections']);
        $this->assertSame('Informasi yang Kami Kumpulkan', $privacy->legal['sections'][0]['title']);
        $this->assertCount(4, $privacy->legal['sections'][1]['cards']);
        $this->assertCount(2, $privacy->legal['sections'][2]['cards']);
        $this->assertCount(3, $privacy->legal['sections'][4]['cards']);

        $this->assertSame(CustomPageTemplate::Legal, $terms->template);
        $this->assertCount(8, $terms->legal['sections']);
        $this->assertCount(3, $terms->legal['sections'][4]['cards']);
        $this->assertSame('Pendahuluan', $terms->legal['sections'][0]['label']);
    }

    public function test_default_pages_have_no_date_label_and_no_pdf(): void
    {
        foreach (CustomPage::query()->whereIn('slug', ['kebijakan-privasi', 'syarat-ketentuan'])->get() as $page) {
            $this->assertNull($page->legal['pdf_path']);
            $this->assertStringNotContainsString('Berlaku Efektif', json_encode($page->legal));
            $this->assertStringNotContainsString('Terakhir Diperbarui', json_encode($page->legal));
        }
    }

    public function test_card_icons_are_all_in_the_curated_list(): void
    {
        foreach (CustomPage::query()->whereIn('slug', ['kebijakan-privasi', 'syarat-ketentuan'])->get() as $page) {
            foreach ($page->legal['sections'] as $section) {
                foreach ($section['cards'] as $card) {
                    $this->assertContains($card['icon'], MaterialSymbolsIcons::keys());
                }
            }
        }
    }

    public function test_placeholders_are_filled_from_site_settings(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Contoh Surya';
        $site->company_email = 'info@contoh.test';
        $site->company_phone = '0812-0000-1111';
        $site->company_address = 'Jl. Contoh No. 1';
        $site->save();

        CustomPage::query()->delete();
        LegalPageInstaller::install();

        $json = json_encode(CustomPage::query()->get()->pluck('legal')->all());

        $this->assertStringContainsString('Contoh Surya', $json);
        $this->assertStringContainsString('info@contoh.test', $json);
        $this->assertStringContainsString('0812-0000-1111', $json);
        $this->assertStringContainsString('Jl. Contoh No. 1', $json);
        $this->assertStringNotContainsString('SUOER', $json);
        $this->assertDoesNotMatchRegularExpression('/\{(app_name|company_[a-z]+)\}/', $json);
    }

    public function test_fallback_values_are_used_when_company_data_is_empty(): void
    {
        $site = app(SiteSettings::class);
        $site->company_email = null;
        $site->company_phone = null;
        $site->company_address = null;
        $site->save();

        CustomPage::query()->delete();
        LegalPageInstaller::install();

        $privacy = CustomPage::query()->where('slug', 'kebijakan-privasi')->firstOrFail();

        $this->assertStringContainsString('hello@suoer.id', json_encode($privacy->legal));
        $this->assertStringContainsString('(021) 5890-7722', json_encode($privacy->legal));
    }

    public function test_installer_is_idempotent_and_never_overwrites_edited_pages(): void
    {
        CustomPage::query()->where('slug', 'kebijakan-privasi')->update(['title' => 'Judul Admin']);

        LegalPageInstaller::install();
        LegalPageInstaller::install();

        $this->assertSame(2, CustomPage::query()->whereIn('slug', ['kebijakan-privasi', 'syarat-ketentuan'])->count());
        $this->assertSame('Judul Admin', CustomPage::query()->where('slug', 'kebijakan-privasi')->value('title'));
    }

    public function test_installer_recreates_a_missing_page_only(): void
    {
        CustomPage::query()->where('slug', 'syarat-ketentuan')->delete();

        $this->seed(LegalPageSeeder::class);

        $this->assertSame(2, CustomPage::query()->whereIn('slug', ['kebijakan-privasi', 'syarat-ketentuan'])->count());
    }

    public function test_footer_links_resolve_to_the_installed_pages(): void
    {
        $this->get('/halaman/kebijakan-privasi')->assertOk();
        $this->get('/halaman/syarat-ketentuan')->assertOk();
    }
}
