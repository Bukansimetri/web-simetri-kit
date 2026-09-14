<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ArticleCategorySeeder::class);
        $this->call(ArticleSeeder::class);
        $this->call(JobOpeningSeeder::class);
        $this->call(FaqItemSeeder::class);
        $this->call(MenuSeeder::class);

        // Konten demo (Layanan/Produk, Tim, Testimoni, Portfolio) SENGAJA
        // tidak dipanggil di sini — lihat `php artisan demo:seed` (AMC-229,
        // spec 019-demo-content-seeder). Standar deployment kit ini
        // melarang konten demo otomatis ikut ter-seed di instalasi produksi.

        // User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $admin->assignRole('super_admin');
    }
}
