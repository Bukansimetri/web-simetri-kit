<?php

namespace Tests\Feature\Public;

use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Mode pemeliharaan menutup halaman publik bagi pengunjung anonim, tapi
 * tidak menyentuh panel admin maupun pengguna yang sedang login (FR-010
 * sampai FR-014, contracts §7).
 */
class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_visitor_sees_maintenance_page_when_enabled(): void
    {
        $settings = app(SiteSettings::class);
        $settings->maintenance_mode = true;
        $settings->save();

        $response = $this->get('/');

        $response->assertStatus(503);
        $response->assertSee('Pemeliharaan', escape: false);
    }

    public function test_maintenance_applies_to_any_public_route(): void
    {
        $settings = app(SiteSettings::class);
        $settings->maintenance_mode = true;
        $settings->save();

        $this->get('/produk')->assertStatus(503);
        $this->get('/kontak')->assertStatus(503);
    }

    public function test_admin_panel_remains_accessible_during_maintenance(): void
    {
        $settings = app(SiteSettings::class);
        $settings->maintenance_mode = true;
        $settings->save();

        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('Mode Pemeliharaan aktif', escape: false);
    }

    public function test_authenticated_user_sees_real_site_during_maintenance(): void
    {
        $settings = app(SiteSettings::class);
        $settings->maintenance_mode = true;
        $settings->save();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertDontSee('Pemeliharaan', escape: false);
    }

    public function test_site_is_normal_when_maintenance_disabled(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
