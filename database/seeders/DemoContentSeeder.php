<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orkestrator konten demo untuk showcase ke calon klien (AMC-229, spec
 * 019-demo-content-seeder). Dipanggil HANYA lewat `demo:seed` — TIDAK
 * PERNAH lewat DatabaseSeeder/app:setup-client (FR-003).
 *
 * Urutan mengikuti data-model.md §Urutan operasi: PortfolioDemoSeeder
 * membuat kategori portfolio lebih dulu (dipakai proyek portfolio di
 * seeder yang sama), lalu Product/TeamMember/Testimonial independen.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BannerSeeder::class,
            PortfolioDemoSeeder::class,
            ProductSeeder::class,
            TeamMemberSeeder::class,
            TestimonialSeeder::class,
            MenuItemDemoSeeder::class,
        ]);
    }
}
