<?php

namespace Tests\Feature\Admin;

use App\Enums\CtaPlacement;
use App\Filament\Resources\CallToActionResource;
use App\Filament\Resources\CallToActionResource\Pages\EditCallToAction;
use App\Filament\Resources\CallToActionResource\Pages\ListCallToActions;
use App\Models\CallToAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CallToActionResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function cta(CtaPlacement $placement): CallToAction
    {
        return CallToAction::query()->where('placement', $placement->value)->firstOrFail();
    }

    public function test_list_shows_all_nine_placements(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListCallToActions::class)
            ->assertOk()
            ->assertCanSeeTableRecords(CallToAction::all())
            ->assertCountTableRecords(9);
    }

    public function test_placements_cannot_be_created_or_deleted(): void
    {
        $this->assertFalse(CallToActionResource::canCreate());
        $this->assertFalse(CallToActionResource::canDelete($this->cta(CtaPlacement::Faq)));
        $this->assertArrayNotHasKey('create', CallToActionResource::getPages());

        Livewire::actingAs($this->admin())
            ->test(ListCallToActions::class)
            ->assertActionDoesNotExist('create')
            ->assertTableActionDoesNotExist('delete');
    }

    public function test_admin_can_edit_faq_cta(): void
    {
        $faq = $this->cta(CtaPlacement::Faq);

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $faq->getRouteKey()])
            ->fillForm(['title' => 'Butuh bantuan lain?', 'body' => 'Kami siap membantu.', 'primary_label' => 'Kontak Kami'])
            ->call('save')
            ->assertHasNoFormErrors();

        $faq->refresh();
        $this->assertSame('Butuh bantuan lain?', $faq->title);
        $this->assertSame(CtaPlacement::Faq, $faq->placement);
    }

    public function test_required_fields_and_lengths_are_validated(): void
    {
        $faq = $this->cta(CtaPlacement::Faq);

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $faq->getRouteKey()])
            ->fillForm(['title' => '', 'primary_label' => ''])
            ->call('save')
            ->assertHasFormErrors(['title' => 'required', 'primary_label' => 'required']);

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $faq->getRouteKey()])
            ->fillForm(['title' => str_repeat('a', 81), 'body' => str_repeat('b', 301), 'primary_label' => str_repeat('c', 41)])
            ->call('save')
            ->assertHasFormErrors(['title' => 'max', 'body' => 'max', 'primary_label' => 'max']);
    }

    public function test_secondary_label_only_exists_and_is_required_for_home(): void
    {
        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $this->cta(CtaPlacement::Faq)->getRouteKey()])
            ->assertFormFieldIsHidden('secondary_label');

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $this->cta(CtaPlacement::Home)->getRouteKey()])
            ->assertFormFieldIsVisible('secondary_label')
            ->fillForm(['secondary_label' => ''])
            ->call('save')
            ->assertHasFormErrors(['secondary_label' => 'required']);
    }
}
