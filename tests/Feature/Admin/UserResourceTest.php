<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Resource baru untuk mengelola akun admin panel dan penetapan peran.
 * Dibatasi super_admin karena bisa dipakai memberi peran super_admin ke
 * siapa pun (celah eskalasi privilege bila dibiarkan terbuka) — pola sama
 * dengan ScriptSettingsPage (lihat SettingsPagesAccessTest).
 */
class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_super_admin_can_list_users(): void
    {
        $admin = $this->makeSuperAdmin();

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertSuccessful();
    }

    public function test_super_admin_can_create_user_with_role(): void
    {
        $admin = $this->makeSuperAdmin();
        $editorRole = Role::firstOrCreate(['name' => 'Editor']);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Budi Santoso',
                'email' => 'budi@example.test',
                'password' => 'password123',
                'roles' => [$editorRole->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::where('email', 'budi@example.test')->first();

        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('Editor'));
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_creating_user_without_any_role_is_rejected(): void
    {
        $admin = $this->makeSuperAdmin();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Tanpa Peran',
                'email' => 'tanpaperan@example.test',
                'password' => 'password123',
                'roles' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['roles']);
    }

    public function test_editing_user_without_filling_password_keeps_old_password(): void
    {
        $admin = $this->makeSuperAdmin();
        $target = User::factory()->create(['password' => 'original-secret']);
        $originalHash = $target->password;
        $role = Role::firstOrCreate(['name' => 'Viewer']);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $target->getRouteKey()])
            ->fillForm([
                'name' => 'Nama Baru',
                'email' => $target->email,
                'password' => null,
                'roles' => [$role->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($originalHash, $target->fresh()->password);
        $this->assertSame('Nama Baru', $target->fresh()->name);
    }

    public function test_regular_admin_cannot_see_user_resource_in_navigation(): void
    {
        Role::firstOrCreate(['name' => 'Editor']);
        $user = User::factory()->create();
        $user->assignRole('Editor');

        $this->actingAs($user);

        $this->assertFalse(UserResource::shouldRegisterNavigation());
    }

    public function test_regular_admin_is_denied_opening_user_resource_directly(): void
    {
        Role::firstOrCreate(['name' => 'Editor']);
        $user = User::factory()->create();
        $user->assignRole('Editor');

        Livewire::actingAs($user)
            ->test(ListUsers::class)
            ->assertForbidden();
    }

    public function test_super_admin_cannot_delete_own_account_from_table(): void
    {
        $admin = $this->makeSuperAdmin();
        User::factory()->create();

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertTableActionHidden('delete', $admin);
    }
}
