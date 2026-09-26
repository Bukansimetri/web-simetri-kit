<?php

namespace Tests\Feature\Dashboard;

use App\Filament\Resources\CalculatorLeadResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Filament\Widgets\LeadStatsOverview;
use App\Models\CalculatorLead;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_widget_shows_labels_and_values(): void
    {
        $user = $this->actingUser();

        ContactSubmission::factory()->count(3)->create(['status' => ContactSubmission::STATUS_NEW]);
        CalculatorLead::factory()->count(2)->create(['status' => CalculatorLead::STATUS_NEW]);
        CalculatorLead::factory()->won()->create();

        Livewire::actingAs($user)
            ->test(LeadStatsOverview::class)
            ->assertSee('Pesan masuk baru')
            ->assertSee('Lead kalkulator baru')
            ->assertSee('Prospek 30 hari')
            ->assertSee('Konversi lead kalkulator')
            ->assertSee('3')
            ->assertSee('2');
    }

    public function test_widget_shows_empty_state_without_leads(): void
    {
        $user = $this->actingUser();

        Livewire::actingAs($user)
            ->test(LeadStatsOverview::class)
            ->assertSee('Belum ada data');
    }

    public function test_widget_stat_cards_link_to_filtered_lists(): void
    {
        $user = $this->actingUser();

        $contactUrl = ContactSubmissionResource::getUrl('index', ['tableFilters' => ['status' => ['value' => ContactSubmission::STATUS_NEW]]]);
        $calculatorUrl = CalculatorLeadResource::getUrl('index', ['tableFilters' => ['status' => ['value' => CalculatorLead::STATUS_NEW]]]);

        Livewire::actingAs($user)
            ->test(LeadStatsOverview::class)
            ->assertSee($contactUrl, escape: false)
            ->assertSee($calculatorUrl, escape: false);
    }

    public function test_filtered_contact_list_shows_only_new_status(): void
    {
        $user = $this->actingUser();

        ContactSubmission::factory()->create(['name' => 'Nama Prospek Baru', 'status' => ContactSubmission::STATUS_NEW]);
        ContactSubmission::factory()->create(['name' => 'Nama Sudah Dihubungi', 'status' => ContactSubmission::STATUS_CONTACTED]);

        $url = ContactSubmissionResource::getUrl('index', ['tableFilters' => ['status' => ['value' => ContactSubmission::STATUS_NEW]]]);

        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $response->assertSee('Nama Prospek Baru');
        $response->assertDontSee('Nama Sudah Dihubungi');
    }
}
