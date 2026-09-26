<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Halaman edit Role (Filament Shield) harus terbuka: Shield membangun
 * daftar permission widget dengan memanggil getHeading() tiap widget, jadi
 * widget yang melakukan query eksternal di getHeading() akan merusak halaman.
 */
class RoleResourceEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_edit_page_loads_for_super_admin(): void
    {
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
