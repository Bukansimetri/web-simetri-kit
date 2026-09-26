<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Halaman edit Role (Filament Shield) tidak boleh error saat Google
 * Analytics belum dikonfigurasi: Shield memanggil getHeading() tiap widget
 * untuk label permission, dan widget GA melakukan query ke API-nya.
 */
class RoleResourceEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_edit_page_loads_when_analytics_is_not_configured(): void
    {
        config(['analytics.property_id' => null]);

        $role = Role::create(['name' => 'super_admin']);
        foreach (['view_any_role', 'view_role', 'update_role'] as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get("/admin/shield/roles/{$role->getKey()}/edit")
            ->assertOk();
    }
}
