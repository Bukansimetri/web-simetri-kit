<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\NewsletterSubscriberResource;
use App\Filament\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_search_subscribers(): void
    {
        $user = User::factory()->create();
        $budi = NewsletterSubscriber::factory()->create(['email' => 'budi@example.com']);
        $sari = NewsletterSubscriber::factory()->create(['email' => 'sari@example.com']);

        Livewire::actingAs($user)
            ->test(ListNewsletterSubscribers::class)
            ->assertCanSeeTableRecords([$budi, $sari])
            ->searchTable('budi@')
            ->assertCanSeeTableRecords([$budi])
            ->assertCanNotSeeTableRecords([$sari]);
    }

    public function test_admin_can_delete_a_subscriber(): void
    {
        $user = User::factory()->create();
        $subscriber = NewsletterSubscriber::factory()->create();

        Livewire::actingAs($user)
            ->test(ListNewsletterSubscribers::class)
            ->callTableAction('delete', $subscriber);

        $this->assertModelMissing($subscriber);
    }

    public function test_resource_has_no_create_or_edit_page(): void
    {
        $this->assertFalse(NewsletterSubscriberResource::canCreate());
        $this->assertSame(['index'], array_keys(NewsletterSubscriberResource::getPages()));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(NewsletterSubscriberResource::getUrl('index'))->assertRedirect();
    }
}
