<?php

namespace Tests\Feature\Pages;

use App\Enums\PageSection;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class PageContentRenderTest extends TestCase
{
    use RefreshDatabase;

    private function whyChooseFragment(string $html): string
    {
        return LegacyMarkup::extract($html, LegacyMarkup::FRAGMENTS['home-why-choose'][1]);
    }

    public function test_why_choose_renders_default_cards(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder(['Efisien &amp; Terjangkau', 'Garansi Panjang', 'Ramah Lingkungan'], escape: false);
    }

    public function test_why_choose_shows_admin_edits(): void
    {
        SectionItem::query()->where('title', 'Efisien & Terjangkau')->update(['title' => 'Hemat Hingga 80%']);

        $this->get('/')->assertOk()->assertSee('Hemat Hingga 80%', escape: false);
    }

    public function test_why_choose_is_hidden_without_active_items(): void
    {
        SectionItem::query()->forSection(PageSection::WhyChoose)->update(['is_active' => false]);

        $this->get('/')->assertOk()->assertDontSee('Mengapa Beralih', escape: false);
    }

    public function test_why_choose_escapes_admin_text(): void
    {
        SectionItem::query()->where('title', 'Garansi Panjang')->update(['title' => '<script>alert(1)</script>']);

        $this->get('/')
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
            ->assertDontSee('<script>alert(1)</script>', escape: false);
    }

    public function test_why_choose_omits_subtitle_paragraph_when_empty(): void
    {
        SectionHeading::query()->where('section', PageSection::WhyChoose->value)->update(['subtitle' => null]);

        $fragment = $this->whyChooseFragment($this->get('/')->getContent());

        $this->assertStringNotContainsString('text-secondary', $fragment);
        $this->assertStringNotContainsString('Investasi cerdas', $fragment);
    }

    public function test_section_text_is_rendered_exactly_as_admin_typed_regardless_of_site_name(): void
    {
        SectionHeading::query()->where('section', PageSection::WhyChoose->value)->update(['title' => "Mengapa Beralih\nBersama Kami {app_name}?"]);

        $site = app(SiteSettings::class);
        $site->site_name = 'Merek Lain';
        $site->save();

        $fragment = $this->whyChooseFragment($this->get('/')->getContent());

        $this->assertStringContainsString('Mengapa Beralih<br>Bersama Kami {app_name}?', $fragment);
        $this->assertStringNotContainsString('Merek Lain', $fragment);
    }

    private function howItWorksFragment(): string
    {
        return LegacyMarkup::extract($this->get('/')->getContent(), LegacyMarkup::FRAGMENTS['home-how-it-works'][1]);
    }

    public function test_how_it_works_renders_step_numbers(): void
    {
        $fragment = $this->howItWorksFragment();

        foreach (['01', '02', '03', '04'] as $number) {
            $this->assertStringContainsString('">'.$number.'</div>', $fragment);
        }
    }

    public function test_how_it_works_decoration_follows_position_not_item(): void
    {
        SectionItem::query()->where('title', 'Inverter')->update(['order' => 0]);
        SectionItem::query()->where('title', 'Panel & PV Cell')->update(['order' => 5]);

        $fragment = $this->howItWorksFragment();

        $this->assertMatchesRegularExpression(
            '/relative group md:mt-0 .*?>01<\/div><div class="[^"]*-rotate-1 [^"]*"><h4[^>]*>Inverter<\/h4>/',
            $fragment,
        );
    }

    public function test_how_it_works_emphasized_step_has_highlighted_number(): void
    {
        $this->assertMatchesRegularExpression(
            '/bg-primary-container">03<\/div><div[^>]*><h4[^>]*>Inverter<\/h4>/',
            $this->howItWorksFragment(),
        );
    }

    public function test_how_it_works_is_hidden_without_active_items(): void
    {
        SectionItem::query()->forSection(PageSection::HowItWorks)->update(['is_active' => false]);

        $this->get('/')->assertOk()->assertDontSee('Sederhana dan Mulus', escape: false);
    }

    public function test_inactive_emphasized_card_does_not_highlight_any_card(): void
    {
        SectionItem::query()->where('title', 'Garansi Panjang')->update(['is_active' => false]);

        $fragment = $this->whyChooseFragment($this->get('/')->assertOk()->getContent());

        $this->assertStringNotContainsString('bg-primary-container shadow-lg', $fragment);
        $this->assertStringNotContainsString('Garansi Panjang', $fragment);
    }

    public function test_page_renders_without_any_emphasized_card(): void
    {
        SectionItem::query()->forSection(PageSection::WhyChoose)->update(['is_emphasized' => false]);

        $fragment = $this->whyChooseFragment($this->get('/')->assertOk()->getContent());

        $this->assertStringNotContainsString('bg-primary-container shadow-lg', $fragment);
    }

    private function careerPage(): string
    {
        $site = app(SiteSettings::class);
        $site->career_module_enabled = true;
        $site->save();

        return $this->get('/karir')->assertOk()->getContent();
    }

    public function test_career_values_render_cards_with_icons(): void
    {
        $fragment = LegacyMarkup::extract($this->careerPage(), LegacyMarkup::FRAGMENTS['karir-values'][1]);

        foreach (['lightbulb', 'groups', 'public', 'Inovasi Berkelanjutan', 'Kolaborasi Tim', 'Dampak Nyata'] as $text) {
            $this->assertStringContainsString($text, $fragment);
        }
    }

    public function test_recruitment_process_renders_plain_step_numbers(): void
    {
        $fragment = LegacyMarkup::extract($this->careerPage(), LegacyMarkup::FRAGMENTS['karir-recruitment'][1]);

        $this->assertMatchesRegularExpression('/>1<\/div><h4[^>]*>Lamar<\/h4>/', $fragment);
        $this->assertMatchesRegularExpression('/>4<\/div><h4[^>]*>Penawaran<\/h4>/', $fragment);
    }

    public function test_recruitment_process_box_is_hidden_without_active_steps(): void
    {
        SectionItem::query()->forSection(PageSection::RecruitmentProcess)->update(['is_active' => false]);

        $html = $this->careerPage();

        $this->assertStringNotContainsString('Proses Rekrutmen', $html);
        $this->assertStringContainsString('Mengapa Bergabung', $html);
    }

    public function test_career_values_hidden_without_active_cards_while_positions_remain(): void
    {
        SectionItem::query()->forSection(PageSection::CareerValues)->update(['is_active' => false]);

        $html = $this->careerPage();

        $this->assertStringNotContainsString('Mengapa Bergabung', $html);
        $this->assertStringContainsString('Posisi Terbuka', $html);
    }
}
