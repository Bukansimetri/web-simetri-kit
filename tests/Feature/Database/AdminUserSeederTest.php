<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kredensial akun super_admin awal dibaca dari config('seeding.super_admin')
 * (bersumber dari .env), bukan ditulis mati — Prinsip I konstitusi.
 * Seeder harus idempoten dan tidak pernah diam-diam memakai password
 * bawaan yang bisa ditebak di lingkungan production.
 */
class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_creates_admin_with_configured_credentials(): void
    {
        config(['seeding.super_admin' => [
            'name' => 'Owner Klien Uji',
            'email' => 'owner@klienuji.test',
            'password' => 'rahasia-klien-123',
        ]]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'owner@klienuji.test')->first();

        $this->assertNotNull($admin);
        $this->assertSame('Owner Klien Uji', $admin->name);
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue(Hash::check('rahasia-klien-123', $admin->password));
    }

    public function test_falls_back_to_password_literal_outside_production(): void
    {
        config(['seeding.super_admin' => [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => null,
        ]]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_generates_random_password_in_production_when_not_configured(): void
    {
        app()->detectEnvironment(fn () => 'production');

        config(['seeding.super_admin' => [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => null,
        ]]);

        // Panggil seeder langsung (bukan lewat $this->seed()/Artisan db:seed)
        // — db:seed punya guard konfirmasi tersendiri untuk environment
        // production yang tidak relevan diuji di sini; yang diuji adalah
        // logika AdminUserSeeder::run() itu sendiri.
        app(AdminUserSeeder::class)->run();

        $admin = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertFalse(Hash::check('password', $admin->password));
    }

    public function test_seeding_twice_does_not_duplicate_or_change_existing_password(): void
    {
        config(['seeding.super_admin' => [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password-pertama',
        ]]);
        $this->seed(AdminUserSeeder::class);

        $firstHash = User::where('email', 'admin@example.com')->first()->password;

        config(['seeding.super_admin' => [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password-kedua-seharusnya-diabaikan',
        ]]);
        $this->seed(AdminUserSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@example.com')->count());
        $this->assertSame($firstHash, User::where('email', 'admin@example.com')->first()->password);
    }

    public function test_existing_account_without_super_admin_role_is_granted_it(): void
    {
        config(['seeding.super_admin' => [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'sembarang',
        ]]);

        $existing = User::factory()->create(['email' => 'admin@example.com']);
        $this->assertFalse($existing->hasRole('super_admin'));

        $this->seed(AdminUserSeeder::class);

        $this->assertTrue($existing->fresh()->hasRole('super_admin'));
    }
}
