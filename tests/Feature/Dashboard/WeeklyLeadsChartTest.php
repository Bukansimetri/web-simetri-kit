<?php

namespace Tests\Feature\Dashboard;

use App\Filament\Widgets\WeeklyLeadsChart;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WeeklyLeadsChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_shows_heading_and_two_dataset_series(): void
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->travelTo('2026-09-10 12:00:00 UTC');
        ContactSubmission::factory()->create();

        $response = Livewire::actingAs($user)->test(WeeklyLeadsChart::class);

        $response->assertSee('Prospek per minggu');
        $response->assertSeeHtml('Pesan Masuk');
        $response->assertSeeHtml('Lead Kalkulator');
        $response->assertSeeHtml('7 Sep');
    }
}
