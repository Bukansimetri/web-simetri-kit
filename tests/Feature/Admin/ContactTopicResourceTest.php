<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ContactTopicResource\Pages\ManageContactTopics;
use App\Models\ContactTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactTopicResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_page_and_see_seeded_topics(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageContactTopics::class)
            ->assertOk()
            ->assertSee('Konsultasi Umum')
            ->assertSee('Pompa Air Tenaga Surya');
    }

    public function test_admin_can_create_topic(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageContactTopics::class)
            ->mountAction('create')
            ->setActionData([
                'name' => 'Instalasi Baru',
                'slug' => 'instalasi-baru',
                'order' => 9,
                'is_active' => true,
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('contact_topics', ['slug' => 'instalasi-baru', 'name' => 'Instalasi Baru']);
    }

    public function test_slug_must_be_unique(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageContactTopics::class)
            ->mountAction('create')
            ->setActionData(['name' => 'Residensial 2', 'slug' => 'residensial'])
            ->callMountedAction()
            ->assertHasActionErrors(['slug']);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageContactTopics::class)
            ->mountAction('create')
            ->setActionData(['name' => '', 'slug' => ''])
            ->callMountedAction()
            ->assertHasActionErrors(['name', 'slug']);
    }

    public function test_admin_can_delete_topic(): void
    {
        $topic = ContactTopic::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ManageContactTopics::class)
            ->callTableAction('delete', $topic);

        $this->assertModelMissing($topic);
    }
}
