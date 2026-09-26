<?php

namespace Tests\Feature\Dashboard;

use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_dashboard_shows_lead_widgets_without_default_filament_widgets(): void
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('filamentphp.com', escape: false);
        $response->assertSee('Pesan masuk baru');
        $response->assertSee('Perlu ditindaklanjuti');
        $response->assertSee('Prospek per minggu');
    }

    public function test_dashboard_shows_empty_states_on_fresh_install(): void
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('Belum ada data');
        $response->assertSee('Semua prospek sudah ditindaklanjuti');
    }

    public function test_dashboard_hides_lead_widgets_when_resource_access_is_denied(): void
    {
        $user = $this->actingUser();

        $policy = new class
        {
            public function viewAny(User $user): bool
            {
                return false;
            }
        };

        Gate::policy(ContactSubmission::class, $policy::class);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('Pesan masuk baru');
        $response->assertDontSee('Perlu ditindaklanjuti');
        $response->assertDontSee('Prospek per minggu');
    }
}
