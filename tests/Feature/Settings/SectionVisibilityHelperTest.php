<?php

namespace Tests\Feature\Settings;

use App\Enums\PublicSection;
use App\Settings\SectionVisibilitySettings;
use App\Support\PageContent\SectionVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SectionVisibilityHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_section_is_shown_by_default(): void
    {
        foreach (PublicSection::cases() as $section) {
            $this->assertTrue(SectionVisibility::shows($section), $section->value);
        }

        $this->assertSame([], SectionVisibility::hiddenSections());
    }

    public function test_hidden_sections_are_not_shown_and_others_are(): void
    {
        SectionVisibility::setHidden([PublicSection::HomeTestimonials, PublicSection::AboutCta]);

        $this->assertFalse(SectionVisibility::shows(PublicSection::HomeTestimonials));
        $this->assertFalse(SectionVisibility::shows(PublicSection::AboutCta));
        $this->assertTrue(SectionVisibility::shows(PublicSection::AboutTestimonials));
        $this->assertCount(2, SectionVisibility::hiddenSections());
    }

    public function test_set_hidden_deduplicates_and_can_be_cleared(): void
    {
        SectionVisibility::setHidden([PublicSection::FaqCta, PublicSection::FaqCta]);

        $this->assertSame(['faq.cta'], app(SectionVisibilitySettings::class)->hidden);

        SectionVisibility::setHidden([]);

        $this->assertSame([], app(SectionVisibilitySettings::class)->hidden);
        $this->assertTrue(SectionVisibility::shows(PublicSection::FaqCta));
    }

    public function test_unknown_keys_in_settings_are_ignored(): void
    {
        $settings = app(SectionVisibilitySettings::class);
        $settings->hidden = ['bukan.section', 'beranda.cta', 123];
        $settings->save();
        SectionVisibility::flush();

        $this->assertSame([PublicSection::HomeCta], SectionVisibility::hiddenSections());
        $this->assertTrue(SectionVisibility::shows(PublicSection::HomeWhyChoose));
    }

    public function test_missing_settings_row_means_everything_is_shown_and_site_still_renders(): void
    {
        DB::table('settings')->where('group', 'section_visibility')->delete();
        SectionVisibility::flush();

        foreach (PublicSection::cases() as $section) {
            $this->assertTrue(SectionVisibility::shows($section));
        }

        $this->get('/')->assertOk();
        $this->get('/tentang-kami')->assertOk();
    }
}
