<?php

namespace Tests\Feature\Admin;

use App\Enums\CtaPlacement;
use App\Filament\Resources\CallToActionResource;
use App\Filament\Resources\CallToActionResource\Pages\EditCallToAction;
use App\Filament\Resources\CallToActionResource\Pages\ListCallToActions;
use App\Models\CallToAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_image_field_only_exists_for_product_detail(): void
    {
        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $this->cta(CtaPlacement::ProductDetail)->getRouteKey()])
            ->assertFormFieldIsVisible('image_path');

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $this->cta(CtaPlacement::Faq)->getRouteKey()])
            ->assertFormFieldIsHidden('image_path');
    }

    public function test_admin_can_upload_product_detail_image_as_webp(): void
    {
        Storage::fake('public');
        $cta = $this->cta(CtaPlacement::ProductDetail);

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $cta->getRouteKey()])
            ->fillForm(['image_path' => UploadedFile::fake()->image('cta.jpg', 1920, 1080)])
            ->call('save')
            ->assertHasNoFormErrors();

        $cta->refresh();
        $this->assertNotNull($cta->image_path);
        $this->assertStringStartsWith('cta/', $cta->image_path);
        $this->assertStringEndsWith('.webp', $cta->image_path);
        Storage::disk('public')->assertExists($cta->image_path);
    }

    public function test_image_is_optional(): void
    {
        $cta = $this->cta(CtaPlacement::ProductDetail);

        Livewire::actingAs($this->admin())
            ->test(EditCallToAction::class, ['record' => $cta->getRouteKey()])
            ->fillForm(['title' => 'Masa Depan Energi'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($cta->refresh()->image_path);
    }
}
