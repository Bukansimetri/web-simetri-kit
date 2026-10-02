<?php

namespace Tests\Feature\Admin;

use App\Enums\PageSection;
use App\Filament\Resources\AboutMissionResource\Pages\EditAboutMission;
use App\Filament\Resources\AboutMissionResource\Pages\ListAboutMissions;
use App\Filament\Resources\AboutTrustResource\Pages\EditAboutTrust;
use App\Filament\Resources\AboutTrustResource\Pages\ListAboutTrusts;
use App\Filament\Resources\AboutValueResource\Pages\ListAboutValues;
use App\Filament\Resources\CareerValueResource\Pages\EditCareerValue;
use App\Filament\Resources\CareerValueResource\Pages\ListCareerValues;
use App\Filament\Resources\HowItWorksStepResource\Pages\EditHowItWorksStep;
use App\Filament\Resources\HowItWorksStepResource\Pages\ListHowItWorksSteps;
use App\Filament\Resources\RecruitmentStepResource\Pages\EditRecruitmentStep;
use App\Filament\Resources\RecruitmentStepResource\Pages\ListRecruitmentSteps;
use App\Filament\Resources\WhyChooseItemResource;
use App\Filament\Resources\WhyChooseItemResource\Pages\CreateWhyChooseItem;
use App\Filament\Resources\WhyChooseItemResource\Pages\EditWhyChooseItem;
use App\Filament\Resources\WhyChooseItemResource\Pages\ListWhyChooseItems;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SectionItemResourcesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_why_choose_list_shows_only_its_own_items(): void
    {
        $own = SectionItem::query()->forSection(PageSection::WhyChoose)->get();
        $others = SectionItem::query()->where('section', '!=', PageSection::WhyChoose->value)->get();

        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->assertOk()
            ->assertCanSeeTableRecords($own)
            ->assertCanNotSeeTableRecords($others);
    }

    public function test_editor_role_can_open_why_choose_list(): void
    {
        Role::create(['name' => 'Editor']);
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $this->actingAs($editor)
            ->get(WhyChooseItemResource::getUrl('index'))
            ->assertOk();
    }

    public function test_admin_can_edit_why_choose_item(): void
    {
        $item = SectionItem::query()->where('title', 'Efisien & Terjangkau')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditWhyChooseItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['title' => 'Hemat Hingga 80%'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Hemat Hingga 80%', $item->refresh()->title);
    }

    public function test_required_fields_are_validated(): void
    {
        $item = SectionItem::query()->forSection(PageSection::WhyChoose)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditWhyChooseItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['title' => '', 'description' => ''])
            ->call('save')
            ->assertHasFormErrors(['title' => 'required', 'description' => 'required']);
    }

    public function test_title_longer_than_sixty_characters_is_rejected(): void
    {
        $item = SectionItem::query()->forSection(PageSection::WhyChoose)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditWhyChooseItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['title' => str_repeat('a', 61)])
            ->call('save')
            ->assertHasFormErrors(['title' => 'max']);
    }

    public function test_icon_outside_curated_list_is_rejected(): void
    {
        $item = SectionItem::query()->forSection(PageSection::WhyChoose)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditWhyChooseItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['icon' => 'not_an_icon'])
            ->call('save')
            ->assertHasFormErrors(['icon']);
    }

    public function test_create_fills_section_and_next_order(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateWhyChooseItem::class)
            ->fillForm([
                'icon' => 'bolt',
                'title' => 'Kartu Baru',
                'description' => 'Deskripsi kartu baru.',
                'is_active' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = SectionItem::query()->where('title', 'Kartu Baru')->firstOrFail();

        $this->assertSame(PageSection::WhyChoose, $created->section);
        $this->assertSame(3, $created->order);
    }

    public function test_how_it_works_list_shows_only_its_steps_and_has_no_icon_field(): void
    {
        $own = SectionItem::query()->forSection(PageSection::HowItWorks)->get();
        $step = $own->firstWhere('title', 'DC Power');

        Livewire::actingAs($this->admin())
            ->test(ListHowItWorksSteps::class)
            ->assertOk()
            ->assertCanSeeTableRecords($own)
            ->assertCanNotSeeTableRecords(SectionItem::query()->forSection(PageSection::WhyChoose)->get());

        Livewire::actingAs($this->admin())
            ->test(EditHowItWorksStep::class, ['record' => $step->getRouteKey()])
            ->assertFormFieldIsHidden('icon')
            ->fillForm(['title' => 'Arus DC'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Arus DC', $step->refresh()->title);
    }

    public function test_career_value_form_has_icon_but_no_emphasis(): void
    {
        $value = SectionItem::query()->forSection(PageSection::CareerValues)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListCareerValues::class)
            ->assertCanSeeTableRecords(SectionItem::query()->forSection(PageSection::CareerValues)->get())
            ->assertCanNotSeeTableRecords(SectionItem::query()->forSection(PageSection::WhyChoose)->get());

        Livewire::actingAs($this->admin())
            ->test(EditCareerValue::class, ['record' => $value->getRouteKey()])
            ->assertFormFieldIsVisible('icon')
            ->assertFormFieldDoesNotExist('is_emphasized');
    }

    public function test_recruitment_step_form_has_neither_icon_nor_emphasis(): void
    {
        $step = SectionItem::query()->forSection(PageSection::RecruitmentProcess)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListRecruitmentSteps::class)
            ->assertCanSeeTableRecords(SectionItem::query()->forSection(PageSection::RecruitmentProcess)->get())
            ->assertCanNotSeeTableRecords(SectionItem::query()->forSection(PageSection::HowItWorks)->get());

        Livewire::actingAs($this->admin())
            ->test(EditRecruitmentStep::class, ['record' => $step->getRouteKey()])
            ->assertFormFieldIsHidden('icon')
            ->assertFormFieldDoesNotExist('is_emphasized');
    }

    public function test_creating_active_card_beyond_limit_is_rejected_but_inactive_is_allowed(): void
    {
        $data = ['icon' => 'bolt', 'title' => 'Kartu Keempat', 'description' => 'Deskripsi.'];

        Livewire::actingAs($this->admin())
            ->test(CreateWhyChooseItem::class)
            ->fillForm([...$data, 'is_active' => true])
            ->call('create')
            ->assertHasFormErrors(['is_active']);

        Livewire::actingAs($this->admin())
            ->test(CreateWhyChooseItem::class)
            ->fillForm([...$data, 'is_active' => false])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(3, SectionItem::query()->forSection(PageSection::WhyChoose)->active()->count());
    }

    public function test_table_toggle_cannot_exceed_active_limit(): void
    {
        $inactive = SectionItem::factory()->forSection(PageSection::WhyChoose)->inactive()->create();

        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->call('updateTableColumnState', 'is_active', (string) $inactive->getKey(), true);

        $this->assertFalse($inactive->refresh()->is_active);
    }

    public function test_table_toggle_activates_when_below_limit(): void
    {
        $existing = SectionItem::query()->where('title', 'Ramah Lingkungan')->firstOrFail();
        $existing->update(['is_active' => false]);
        $inactive = SectionItem::factory()->forSection(PageSection::WhyChoose)->inactive()->create();

        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->call('updateTableColumnState', 'is_active', (string) $inactive->getKey(), true);

        $this->assertTrue($inactive->refresh()->is_active);
    }

    public function test_step_sections_allow_four_and_career_values_three_active_items(): void
    {
        $howItWorks = SectionItem::factory()->forSection(PageSection::HowItWorks)->inactive()->create();
        $recruitment = SectionItem::factory()->forSection(PageSection::RecruitmentProcess)->inactive()->create();
        $careerValue = SectionItem::factory()->forSection(PageSection::CareerValues)->inactive()->create();

        Livewire::actingAs($this->admin())
            ->test(ListHowItWorksSteps::class)
            ->call('updateTableColumnState', 'is_active', (string) $howItWorks->getKey(), true);
        Livewire::actingAs($this->admin())
            ->test(ListRecruitmentSteps::class)
            ->call('updateTableColumnState', 'is_active', (string) $recruitment->getKey(), true);
        Livewire::actingAs($this->admin())
            ->test(ListCareerValues::class)
            ->call('updateTableColumnState', 'is_active', (string) $careerValue->getKey(), true);

        $this->assertFalse($howItWorks->refresh()->is_active);
        $this->assertFalse($recruitment->refresh()->is_active);
        $this->assertFalse($careerValue->refresh()->is_active);
    }

    public function test_editing_an_already_active_item_is_not_blocked_by_limit(): void
    {
        $item = SectionItem::query()->where('title', 'Garansi Panjang')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditWhyChooseItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['description' => 'Deskripsi baru.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Deskripsi baru.', $item->refresh()->description);
    }

    public function test_emphasizing_an_item_releases_the_previous_one_within_section_only(): void
    {
        $item = SectionItem::query()->where('title', 'Efisien & Terjangkau')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditWhyChooseItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['is_emphasized' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $emphasized = SectionItem::query()->forSection(PageSection::WhyChoose)->where('is_emphasized', true)->pluck('title')->all();

        $this->assertSame(['Efisien & Terjangkau'], $emphasized);
        $this->assertTrue(SectionItem::query()->where('title', 'Inverter')->value('is_emphasized'));
    }

    public function test_emphasis_is_never_stored_for_sections_without_emphasis(): void
    {
        $value = SectionItem::query()->forSection(PageSection::CareerValues)->firstOrFail();

        $value->update(['is_emphasized' => true]);

        $this->assertFalse($value->refresh()->is_emphasized);
    }

    public function test_reordering_updates_order_column(): void
    {
        $ids = SectionItem::query()->forSection(PageSection::WhyChoose)->ordered()->pluck('id')->reverse()->values()->map(fn (int $id): string => (string) $id)->all();

        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->call('reorderTable', $ids);

        $this->assertSame(
            ['Ramah Lingkungan', 'Garansi Panjang', 'Efisien & Terjangkau'],
            SectionItem::query()->forSection(PageSection::WhyChoose)->ordered()->pluck('title')->all(),
        );
    }

    public function test_heading_action_is_prefilled_and_saves_multiline_title(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->mountAction('editSectionHeading')
            ->assertActionDataSet(['title' => SectionHeading::query()->where('section', PageSection::WhyChoose->value)->value('title')])
            ->setActionData(['title' => "Baris Satu\nBaris Dua", 'subtitle' => 'Sub baru'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $heading = SectionHeading::query()->where('section', PageSection::WhyChoose->value)->firstOrFail();
        $this->assertSame("Baris Satu\nBaris Dua", $heading->title);
        $this->assertSame('Sub baru', $heading->subtitle);

        $this->get('/')
            ->assertSee('Baris Satu<br>Baris Dua', escape: false)
            ->assertSee('Sub baru', escape: false);
    }

    public function test_heading_title_is_required_and_limited(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->callAction('editSectionHeading', data: ['title' => ''])
            ->assertHasActionErrors(['title' => 'required']);

        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->callAction('editSectionHeading', data: ['title' => str_repeat('a', 81)])
            ->assertHasActionErrors(['title' => 'max']);
    }

    public function test_recruitment_heading_has_no_subtitle_field(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListRecruitmentSteps::class)
            ->mountAction('editSectionHeading')
            ->assertActionDataSet(['title' => 'Proses Rekrutmen'])
            ->assertFormFieldIsHidden('subtitle', 'mountedActionForm');
    }

    /**
     * @return array<string, array{0: class-string, 1: PageSection, 2: string}>
     */
    public static function reorderablePages(): array
    {
        return [
            'beranda-mengapa-beralih' => [ListWhyChooseItems::class, PageSection::WhyChoose, '/'],
            'beranda-cara-kerja' => [ListHowItWorksSteps::class, PageSection::HowItWorks, '/'],
            'karir-mengapa-bergabung' => [ListCareerValues::class, PageSection::CareerValues, '/karir'],
            'karir-proses-rekrutmen' => [ListRecruitmentSteps::class, PageSection::RecruitmentProcess, '/karir'],
            'tentang-kami-misi' => [ListAboutMissions::class, PageSection::AboutMission, '/tentang-kami'],
            'tentang-kami-nilai' => [ListAboutValues::class, PageSection::AboutValues, '/tentang-kami'],
            'tentang-kami-trust' => [ListAboutTrusts::class, PageSection::AboutTrust, '/tentang-kami'],
        ];
    }

    #[DataProvider('reorderablePages')]
    public function test_drag_reorder_in_admin_changes_public_display_order(string $listPage, PageSection $section, string $path): void
    {
        $site = app(SiteSettings::class);
        $site->career_module_enabled = true;
        $site->save();

        $reversed = SectionItem::query()->forSection($section)->ordered()->get()->reverse()->values();

        Livewire::actingAs($this->admin())
            ->test($listPage)
            ->call('reorderTable', $reversed->map(fn (SectionItem $item): string => (string) $item->getKey())->all());

        $this->get($path)
            ->assertOk()
            ->assertSeeInOrder($reversed->map(fn (SectionItem $item): string => e($item->title))->all(), escape: false);
    }

    public function test_about_resources_are_scoped_and_have_correct_limits(): void
    {
        $mission = SectionItem::query()->forSection(PageSection::AboutMission)->get();

        Livewire::actingAs($this->admin())
            ->test(ListAboutMissions::class)
            ->assertOk()
            ->assertCanSeeTableRecords($mission)
            ->assertCanNotSeeTableRecords(SectionItem::query()->forSection(PageSection::AboutValues)->get());

        $extra = SectionItem::factory()->forSection(PageSection::AboutMission)->inactive()->create();
        Livewire::actingAs($this->admin())
            ->test(ListAboutMissions::class)
            ->call('updateTableColumnState', 'is_active', (string) $extra->getKey(), true);
        $this->assertFalse($extra->refresh()->is_active);

        $value = SectionItem::factory()->forSection(PageSection::AboutValues)->inactive()->create();
        Livewire::actingAs($this->admin())
            ->test(ListAboutValues::class)
            ->call('updateTableColumnState', 'is_active', (string) $value->getKey(), true);
        $this->assertFalse($value->refresh()->is_active);

        $trust = SectionItem::factory()->forSection(PageSection::AboutTrust)->inactive()->create();
        Livewire::actingAs($this->admin())
            ->test(ListAboutTrusts::class)
            ->call('updateTableColumnState', 'is_active', (string) $trust->getKey(), true);
        $this->assertFalse($trust->refresh()->is_active);
    }

    public function test_about_item_length_limits_follow_legacy_settings(): void
    {
        $mission = SectionItem::query()->forSection(PageSection::AboutMission)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditAboutMission::class, ['record' => $mission->getRouteKey()])
            ->fillForm(['title' => str_repeat('a', 120), 'description' => str_repeat('b', 500)])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::actingAs($this->admin())
            ->test(EditAboutMission::class, ['record' => $mission->getRouteKey()])
            ->fillForm(['title' => str_repeat('a', 121), 'description' => str_repeat('b', 501)])
            ->call('save')
            ->assertHasFormErrors(['title' => 'max', 'description' => 'max']);

        $trust = SectionItem::query()->forSection(PageSection::AboutTrust)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditAboutTrust::class, ['record' => $trust->getRouteKey()])
            ->fillForm(['title' => str_repeat('a', 61), 'description' => str_repeat('b', 121)])
            ->call('save')
            ->assertHasFormErrors(['title' => 'max', 'description' => 'max']);
    }

    public function test_trust_form_uses_number_and_caption_labels_and_requires_icon(): void
    {
        $trust = SectionItem::query()->forSection(PageSection::AboutTrust)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditAboutTrust::class, ['record' => $trust->getRouteKey()])
            ->assertFormFieldExists('title', fn ($field): bool => $field->getLabel() === 'Angka')
            ->assertFormFieldExists('description', fn ($field): bool => $field->getLabel() === 'Keterangan')
            ->fillForm(['icon' => null])
            ->call('save')
            ->assertHasFormErrors(['icon' => 'required']);
    }

    public function test_trust_has_no_section_heading_action(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListAboutTrusts::class)
            ->assertActionDoesNotExist('editSectionHeading');
    }

    public function test_mission_heading_action_has_eyebrow(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListAboutMissions::class)
            ->callAction('editSectionHeading', data: ['title' => 'Judul', 'subtitle' => 'Sub', 'eyebrow' => 'Eyebrow Baru'])
            ->assertHasNoActionErrors();

        $heading = SectionHeading::query()->where('section', PageSection::AboutMission->value)->firstOrFail();
        $this->assertSame('Eyebrow Baru', $heading->eyebrow);

        Livewire::actingAs($this->admin())
            ->test(ListAboutMissions::class)
            ->callAction('editSectionHeading', data: ['title' => 'Judul', 'eyebrow' => str_repeat('a', 61)])
            ->assertHasActionErrors(['eyebrow' => 'max']);
    }

    public function test_values_heading_action_saves_featured_card_and_requires_its_text(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListAboutValues::class)
            ->callAction('editSectionHeading', data: [
                'title' => 'Nilai Baru',
                'subtitle' => 'Sub',
                'featured_icon' => 'bolt',
                'featured_title' => 'Kartu Besar',
                'featured_description' => 'Deskripsi kartu besar.',
            ])
            ->assertHasNoActionErrors();

        $heading = SectionHeading::query()->where('section', PageSection::AboutValues->value)->firstOrFail();
        $this->assertSame('bolt', $heading->featured_icon);
        $this->assertSame('Kartu Besar', $heading->featured_title);

        Livewire::actingAs($this->admin())
            ->test(ListAboutValues::class)
            ->callAction('editSectionHeading', data: ['title' => 'Nilai', 'featured_icon' => 'bolt', 'featured_title' => '', 'featured_description' => ''])
            ->assertHasActionErrors(['featured_title' => 'required', 'featured_description' => 'required']);
    }

    public function test_other_sections_heading_action_has_no_featured_card_or_eyebrow(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->mountAction('editSectionHeading')
            ->assertFormFieldDoesNotExist('featured_title', 'mountedActionForm')
            ->assertFormFieldDoesNotExist('eyebrow', 'mountedActionForm');
    }

    public function test_admin_can_delete_item(): void
    {
        $item = SectionItem::query()->where('title', 'Ramah Lingkungan')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListWhyChooseItems::class)
            ->callTableAction('delete', $item);

        $this->assertModelMissing($item);
    }
}
