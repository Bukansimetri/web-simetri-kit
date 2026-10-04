<?php

namespace Tests\Feature\Admin;

use App\Enums\PageBlockType;
use App\Filament\Pages\AboutPageSettingsPage;
use App\Filament\Resources\PageBlockResource;
use App\Filament\Resources\PageBlockResource\Pages\EditPageBlock;
use App\Filament\Resources\PageBlockResource\Pages\ListPageBlocks;
use App\Models\PageBlock;
use App\Models\User;
use App\Settings\AboutPageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PageBlockResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function block(PageBlockType $type): PageBlock
    {
        return PageBlock::query()->where('block', $type->value)->firstOrFail();
    }

    private function edit(PageBlockType $type)
    {
        return Livewire::actingAs($this->admin())->test(EditPageBlock::class, ['record' => $this->block($type)->getRouteKey()]);
    }

    public function test_four_blocks_are_listed_and_cannot_be_created_or_deleted(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListPageBlocks::class)
            ->assertOk()
            ->assertCountTableRecords(4)
            ->assertTableActionDoesNotExist('delete');

        $this->assertFalse(PageBlockResource::canCreate());
        $this->assertFalse(PageBlockResource::canDelete($this->block(PageBlockType::AboutHero)));
        $this->assertArrayNotHasKey('create', PageBlockResource::getPages());
    }

    public function test_form_only_shows_fields_of_its_own_block(): void
    {
        $this->edit(PageBlockType::AboutVision)
            ->assertFormFieldExists('data.subtext')
            ->assertFormFieldDoesNotExist('data.badge_text')
            ->assertFormFieldDoesNotExist('data.whatsapp_message');

        $this->edit(PageBlockType::ContactInfo)
            ->assertFormFieldExists('data.operating_hours')
            ->assertFormFieldDoesNotExist('data.heading');

        $this->edit(PageBlockType::AboutHero)
            ->assertFormFieldExists('data.image_path')
            ->assertFormFieldExists('data.subtitle');
    }

    public function test_admin_can_edit_vision_and_values_are_saved(): void
    {
        $this->edit(PageBlockType::AboutVision)
            ->fillForm(['data' => ['eyebrow' => 'Visi Baru', 'heading' => 'Pernyataan visi baru.', 'subtext' => 'Subteks baru.']])
            ->call('save')
            ->assertHasNoFormErrors();

        $data = $this->block(PageBlockType::AboutVision)->data;
        $this->assertSame('Visi Baru', $data['eyebrow']);
        $this->assertSame('Pernyataan visi baru.', $data['heading']);
    }

    public function test_contact_info_requires_label_and_message(): void
    {
        $this->edit(PageBlockType::ContactInfo)
            ->fillForm(['data' => ['whatsapp_label' => '', 'whatsapp_message' => '', 'operating_hours' => 'x']])
            ->call('save')
            ->assertHasFormErrors(['data.whatsapp_label' => 'required', 'data.whatsapp_message' => 'required']);
    }

    public function test_length_limits_are_enforced(): void
    {
        $this->edit(PageBlockType::ContactInfo)
            ->fillForm(['data' => ['whatsapp_label' => str_repeat('a', 41), 'operating_hours' => str_repeat('b', 81), 'whatsapp_message' => str_repeat('c', 301)]])
            ->call('save')
            ->assertHasFormErrors(['data.whatsapp_label' => 'max', 'data.operating_hours' => 'max', 'data.whatsapp_message' => 'max']);

        $this->edit(PageBlockType::AboutVision)
            ->fillForm(['data' => ['eyebrow' => str_repeat('a', 61), 'heading' => str_repeat('b', 501), 'subtext' => str_repeat('c', 501)]])
            ->call('save')
            ->assertHasFormErrors(['data.eyebrow' => 'max', 'data.heading' => 'max', 'data.subtext' => 'max']);
    }

    public function test_uploaded_hero_image_is_stored_as_webp(): void
    {
        Storage::fake('public');

        $this->edit(PageBlockType::AboutHero)
            ->fillForm(['data' => ['image_path' => UploadedFile::fake()->image('hero.jpg', 1200, 600), 'subtitle' => 'Sub']])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = $this->block(PageBlockType::AboutHero)->data['image_path'];

        $this->assertNotEmpty($path);
        $this->assertSame('webp', pathinfo($path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($path);
    }

    public function test_legacy_about_settings_page_is_gone_from_panel(): void
    {
        $this->assertFalse(class_exists(AboutPageSettingsPage::class));
        $this->assertFalse(class_exists(AboutPageSettings::class));
    }
}
