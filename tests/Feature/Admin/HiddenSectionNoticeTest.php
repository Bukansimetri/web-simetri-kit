<?php

namespace Tests\Feature\Admin;

use App\Enums\FaqPlacement;
use App\Enums\PageBlockType;
use App\Enums\PublicSection;
use App\Filament\Pages\SectionVisibilitySettingsPage;
use App\Filament\Resources\AboutMissionResource\Pages\ListAboutMissions;
use App\Filament\Resources\AboutTrustResource\Pages\ListAboutTrusts;
use App\Filament\Resources\AboutValueResource\Pages\ListAboutValues;
use App\Filament\Resources\CallToActionResource\Pages\ListCallToActions;
use App\Filament\Resources\CareerValueResource\Pages\ListCareerValues;
use App\Filament\Resources\ClientLogoResource\Pages\ListClientLogos;
use App\Filament\Resources\FaqItemResource\Pages\ListFaqItems;
use App\Filament\Resources\HowItWorksStepResource\Pages\ListHowItWorksSteps;
use App\Filament\Resources\PageBlockResource\Pages\ListPageBlocks;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\RecruitmentStepResource\Pages\ListRecruitmentSteps;
use App\Filament\Resources\TeamMemberResource\Pages\ListTeamMembers;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Filament\Resources\WhyChooseItemResource\Pages\ListWhyChooseItems;
use App\Models\FaqItem;
use App\Models\User;
use App\Support\PageContent\SectionVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HiddenSectionNoticeTest extends TestCase
{
    use RefreshDatabase;

    private const NOTICE = 'Section ini sedang disembunyikan dari situs';

    /**
     * @return array<string, array{0: class-string, 1: PublicSection}>
     */
    public static function listPages(): array
    {
        return [
            'mengapa beralih' => [ListWhyChooseItems::class, PublicSection::HomeWhyChoose],
            'cara kerja' => [ListHowItWorksSteps::class, PublicSection::HomeHowItWorks],
            'mengapa bergabung' => [ListCareerValues::class, PublicSection::CareerValues],
            'proses rekrutmen' => [ListRecruitmentSteps::class, PublicSection::CareerProcess],
            'misi' => [ListAboutMissions::class, PublicSection::AboutMission],
            'nilai' => [ListAboutValues::class, PublicSection::AboutValues],
            'trust strip' => [ListAboutTrusts::class, PublicSection::AboutTrust],
            'testimoni (beranda)' => [ListTestimonials::class, PublicSection::HomeTestimonials],
            'testimoni (tentang kami)' => [ListTestimonials::class, PublicSection::AboutTestimonials],
            'tim' => [ListTeamMembers::class, PublicSection::AboutTeam],
            'logo klien' => [ListClientLogos::class, PublicSection::AboutClientLogos],
            'produk' => [ListProducts::class, PublicSection::HomeSolutions],
            'cta (beranda)' => [ListCallToActions::class, PublicSection::HomeCta],
            'cta (karir)' => [ListCallToActions::class, PublicSection::CareerCta],
            'blok halaman (siapa kami)' => [ListPageBlocks::class, PublicSection::AboutWhoWeAre],
            'blok halaman (visi)' => [ListPageBlocks::class, PublicSection::AboutVision],
            'faq (produk)' => [ListFaqItems::class, PublicSection::ProductFaq],
            'faq (kontak)' => [ListFaqItems::class, PublicSection::ContactFaq],
        ];
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('listPages')]
    public function test_notice_appears_only_while_the_related_section_is_hidden(string $page, PublicSection $section): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test($page)->assertDontSee(self::NOTICE);

        SectionVisibility::setHidden([$section]);

        Livewire::actingAs($user)->test($page)
            ->assertSee(self::NOTICE)
            ->assertSee($section->fullLabel())
            ->assertSeeHtml(e(SectionVisibilitySettingsPage::getUrl()));

        SectionVisibility::setHidden([]);

        Livewire::actingAs($user)->test($page)->assertDontSee(self::NOTICE);
    }

    public function test_notice_lists_every_hidden_related_section_and_only_those(): void
    {
        $user = User::factory()->create();
        SectionVisibility::setHidden([PublicSection::HomeTestimonials, PublicSection::AboutTestimonials, PublicSection::AboutTeam]);

        Livewire::actingAs($user)->test(ListTestimonials::class)
            ->assertSee('Beranda – Testimoni (Partner Kami)')
            ->assertSee('Tentang Kami – Testimoni (Partner Kami)')
            ->assertDontSee('Tentang Kami – Tim');

        SectionVisibility::setHidden([PublicSection::AboutTestimonials]);

        Livewire::actingAs($user)->test(ListTestimonials::class)
            ->assertSee('Tentang Kami – Testimoni (Partner Kami)')
            ->assertDontSee('Beranda – Testimoni (Partner Kami)');
    }

    public function test_unrelated_hidden_sections_do_not_trigger_the_notice(): void
    {
        SectionVisibility::setHidden([PublicSection::FaqCta]);

        Livewire::actingAs(User::factory()->create())->test(ListWhyChooseItems::class)->assertDontSee(self::NOTICE);
    }

    public function test_cta_table_marks_only_hidden_rows(): void
    {
        $user = User::factory()->create();
        SectionVisibility::setHidden([PublicSection::HomeCta, PublicSection::FaqCta]);

        $html = Livewire::actingAs($user)->test(ListCallToActions::class)->html();

        $this->assertSame(2, substr_count($html, 'Disembunyikan'));
        $this->assertStringContainsString('Tayang', $html);
    }

    public function test_page_block_table_marks_who_we_are_and_vision_only(): void
    {
        $user = User::factory()->create();
        SectionVisibility::setHidden([PublicSection::AboutVision]);

        $html = Livewire::actingAs($user)->test(ListPageBlocks::class)->html();

        $this->assertSame(1, substr_count($html, 'Disembunyikan'));
        $this->assertStringContainsString(PageBlockType::AboutVision->label(), $html);
    }

    public function test_faq_table_marks_rows_by_placement(): void
    {
        $user = User::factory()->create();
        FaqItem::query()->delete();
        FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['question' => 'Tanya produk?']);
        FaqItem::factory()->forPlacement(FaqPlacement::Contact)->create(['question' => 'Tanya kontak?']);
        SectionVisibility::setHidden([PublicSection::ProductFaq]);

        $product = Livewire::actingAs($user)->test(ListFaqItems::class)
            ->filterTable('placement', FaqPlacement::Product->value)->html();
        $contact = Livewire::actingAs($user)->test(ListFaqItems::class)
            ->filterTable('placement', FaqPlacement::Contact->value)->html();

        $this->assertStringContainsString('Tanya produk?', $product);
        $this->assertSame(1, substr_count($product, 'Disembunyikan'));
        $this->assertStringContainsString('Tanya kontak?', $contact);
        $this->assertSame(0, substr_count($contact, 'Disembunyikan'));
    }
}
