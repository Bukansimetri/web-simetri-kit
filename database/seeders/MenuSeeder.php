<?php

namespace Database\Seeders;

use App\Models\MenuLocation;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Seed lokasi menu default. Setiap instalasi klien membutuhkan lokasi
     * ini agar <x-layout.menu> pada header/footer punya tempat untuk
     * dikonfigurasi (FR-003, data-model.md).
     */
    public function run(): void
    {
        MenuLocation::firstOrCreate(
            ['slug' => 'navbar-utama'],
            ['name' => 'Navbar Utama', 'description' => 'Tampil di header seluruh halaman publik']
        );

        MenuLocation::firstOrCreate(
            ['slug' => 'footer'],
            ['name' => 'Footer', 'description' => 'Tampil di footer seluruh halaman publik']
        );
    }
}
