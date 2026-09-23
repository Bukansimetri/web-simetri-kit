<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Membuktikan restrukturisasi navigasi panel admin benar-benar termuat saat
 * runtime — bukan cuma lolos sintaks. Grup lama ("Settings", "Filament
 * Shield") tidak boleh tersisa; grup baru dan penerjemahan Shield/Activity
 * Log ke Bahasa Indonesia harus tampil.
 */
class NavigationStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_with_new_navigation_structure_for_super_admin(): void
    {
        // Shield di project ini pakai `define_via_gate: false` (config/filament-shield.php)
        // — super_admin baru bisa lihat resource Shield (RoleResource) bila
        // benar-benar punya permission-nya, bukan cuma nama role (lihat
        // app/Policies/RolePolicy.php). Meniru apa yang dilakukan
        // `php artisan shield:super-admin` di instalasi sungguhan.
        $role = Role::create(['name' => 'super_admin']);
        Permission::create(['name' => 'view_any_role', 'guard_name' => 'web']);
        $role->givePermissionTo('view_any_role');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();

        // Grup baru harus tampil.
        $response->assertSee('Konten Halaman', escape: false);
        $response->assertSee('Prospek &amp; Pesan', escape: false);
        $response->assertSee('Pengaturan Situs', escape: false);
        $response->assertSee('Sistem', escape: false);

        // Item yang dipindah ke grup baru harus tetap tampil dengan label
        // aslinya (bukti navigationGroup baru benar-benar dipakai, bukan
        // cuma dideklarasikan tanpa dipakai resource manapun).
        $response->assertSee('Lead Kalkulator', escape: false);
        $response->assertSee('Pesan Masuk', escape: false);
        $response->assertSee('Halaman Tentang Kami', escape: false);

        // Terjemahan Shield/Activity Log ke Bahasa Indonesia (APP_LOCALE=id
        // + override lang/vendor/filament-shield/id).
        $response->assertSee('Peran', escape: false);
        $response->assertSee('Log Aktivitas', escape: false);

        // Grup/label lama tidak boleh tersisa.
        $response->assertDontSee('Filament Shield', escape: false);
        $response->assertDontSee('>Roles<', escape: false);
        $response->assertDontSee('>Activity Log<', escape: false);
    }

    public function test_regular_admin_does_not_see_sistem_group_items_requiring_super_admin(): void
    {
        // Panel butuh minimal satu role terdaftar (guard 'web') supaya bisa
        // login sama sekali — user tanpa role apapun ditolak 403 di level
        // panel, terlepas dari perubahan navigasi ini.
        Role::create(['name' => 'Editor']);
        $user = User::factory()->create();
        $user->assignRole('Editor');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('Log Aktivitas', escape: false);
    }
}
