<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\CustomPageResource\Pages\CreateCustomPage;
use App\Filament\Resources\CustomPageResource\Pages\EditCustomPage;
use App\Filament\Resources\CustomPageResource\Pages\ListCustomPages;
use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomPageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListCustomPages::class)
            ->assertOk();
    }

    public function test_admin_can_create_custom_page_and_it_appears_publicly(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Kebijakan Privasi',
                'content' => '<p>Isi kebijakan privasi.</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = CustomPage::where('title', 'Kebijakan Privasi')->first();

        $this->assertNotNull($page);
        $this->assertSame('kebijakan-privasi', $page->slug);

        $this->get('/halaman/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Isi kebijakan privasi.', escape: false);
    }

    public function test_slug_auto_generates_from_title_when_blank(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Syarat Dan Ketentuan',
                'content' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('custom_pages', ['slug' => 'syarat-dan-ketentuan']);
    }

    public function test_manual_slug_override_is_respected(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Kebijakan Privasi',
                'slug' => 'privasi',
                'content' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('custom_pages', ['slug' => 'privasi']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        CustomPage::factory()->create(['slug' => 'halaman-a']);

        Livewire::actingAs(User::factory()->create())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Halaman B',
                'slug' => 'halaman-a',
                'content' => 'x',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => '',
                'content' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'content']);
    }

    public function test_admin_can_edit_custom_page_and_change_is_reflected_publicly(): void
    {
        $page = CustomPage::factory()->create(['title' => 'Judul Lama', 'slug' => 'judul-lama']);

        Livewire::actingAs(User::factory()->create())
            ->test(EditCustomPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'Judul Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Judul Baru', $page->fresh()->title);
        $this->get('/halaman/'.$page->fresh()->slug)->assertOk()->assertSee('Judul Baru', escape: false);
    }

    public function test_admin_can_delete_custom_page_and_slug_returns_404(): void
    {
        $page = CustomPage::factory()->create(['slug' => 'hapus-saya']);

        Livewire::actingAs(User::factory()->create())
            ->test(ListCustomPages::class)
            ->callTableAction('delete', $page);

        $this->assertDatabaseMissing('custom_pages', ['id' => $page->id]);
        $this->get('/halaman/hapus-saya')->assertNotFound();
    }
}
