<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Settings\ScriptSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Isi slot kode hanya dimuat pada halaman publik, tidak pernah di dalam
 * panel admin (FR-046, contracts §4).
 */
class ScriptSlotNotInAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_head_script_does_not_leak_into_admin_dashboard(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->head_scripts = '<!-- PENANDA-TIDAK-BOLEH-DI-ADMIN -->';
        $settings->footer_scripts = '<!-- PENANDA-FOOTER-TIDAK-DI-ADMIN -->';
        $settings->custom_css = '.penanda-css-tidak-di-admin {}';
        $settings->save();

        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('PENANDA-TIDAK-BOLEH-DI-ADMIN', escape: false);
        $response->assertDontSee('PENANDA-FOOTER-TIDAK-DI-ADMIN', escape: false);
        $response->assertDontSee('penanda-css-tidak-di-admin', escape: false);
    }
}
