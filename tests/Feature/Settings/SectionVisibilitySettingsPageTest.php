<?php

namespace Tests\Feature\Settings;

use App\Enums\PublicSection;
use App\Filament\Pages\SectionVisibilitySettingsPage;
use App\Models\User;
use App\Settings\SectionVisibilitySettings;
use App\Settings\SiteSettings;
use App\Support\PageContent\SectionVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SectionVisibilitySettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_page_opens_with_all_25_toggles_on_by_default_grouped_by_page(): void
    {
        $component = Livewire::actingAs($this->admin())->test(SectionVisibilitySettingsPage::class)->assertSuccessful();

        foreach (PublicSection::cases() as $section) {
            $component->assertFormFieldExists(SectionVisibilitySettingsPage::field($section))
                ->assertFormSet([SectionVisibilitySettingsPage::field($section) => true]);
        }

        foreach (PublicSection::pages() as $page) {
            $component->assertSee($page);
        }
    }

    public function test_saving_stores_only_the_switched_off_sections_and_confirms(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->fillForm([
                SectionVisibilitySettingsPage::field(PublicSection::HomeTestimonials) => false,
                SectionVisibilitySettingsPage::field(PublicSection::AboutCta) => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Tampilan section tersimpan');

        $this->assertEqualsCanonicalizing(
            ['beranda.testimoni', 'tentang-kami.cta'],
            app(SectionVisibilitySettings::class)->hidden,
        );
    }

    public function test_switching_back_on_empties_the_hidden_list(): void
    {
        SectionVisibility::setHidden([PublicSection::FaqCta, PublicSection::ContactFaq]);

        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->assertFormSet([
                SectionVisibilitySettingsPage::field(PublicSection::FaqCta) => false,
                SectionVisibilitySettingsPage::field(PublicSection::ContactFaq) => false,
                SectionVisibilitySettingsPage::field(PublicSection::AboutCta) => true,
            ])
            ->fillForm([
                SectionVisibilitySettingsPage::field(PublicSection::FaqCta) => true,
                SectionVisibilitySettingsPage::field(PublicSection::ContactFaq) => true,
            ])
            ->call('save');

        $this->assertSame([], app(SectionVisibilitySettings::class)->hidden);
    }

    public function test_saving_does_not_change_other_settings_groups(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Nama Tetap';
        $site->save();

        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->fillForm([SectionVisibilitySettingsPage::field(PublicSection::HomeCta) => false])
            ->call('save');

        $this->assertSame('Nama Tetap', app(SiteSettings::class)->site_name);
    }

    public function test_saved_change_is_visible_on_the_public_site_immediately(): void
    {
        $this->get('/')->assertOk()->assertSee('Siap beralih ke', escape: false);

        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->fillForm([SectionVisibilitySettingsPage::field(PublicSection::HomeCta) => false])
            ->call('save');

        $this->get('/')->assertOk()->assertDontSee('Siap beralih ke', escape: false);
    }

    public function test_group_title_shows_the_hidden_count_only_when_something_is_hidden(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->assertDontSee('disembunyikan');

        SectionVisibility::setHidden([PublicSection::AboutTeam, PublicSection::AboutVision, PublicSection::HomeCta]);

        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->assertSee('Tentang Kami · 2 disembunyikan')
            ->assertSee('Beranda · 1 disembunyikan')
            ->assertDontSee('Karir · ');
    }

    public function test_group_titles_update_right_after_saving_without_reloading(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SectionVisibilitySettingsPage::class)
            ->assertDontSee('disembunyikan')
            ->fillForm([SectionVisibilitySettingsPage::field(PublicSection::AboutVision) => false])
            ->call('save')
            ->assertSee('Tentang Kami · 1 disembunyikan');
    }

    public function test_every_toggle_links_to_its_content_menu(): void
    {
        $component = Livewire::actingAs($this->admin())->test(SectionVisibilitySettingsPage::class);

        foreach (PublicSection::cases() as $section) {
            $component->assertFormComponentActionExists(
                SectionVisibilitySettingsPage::field($section),
                'edit_'.$section->name,
            );
        }

        $html = $component->html();

        $this->assertStringContainsString('Edit isi', $html);
        $this->assertStringContainsString(e(PublicSection::HomeWhyChoose->contentUrl()), $html);
        $this->assertStringContainsString(e(PublicSection::ProductFaq->contentUrl()), $html);
    }
}
