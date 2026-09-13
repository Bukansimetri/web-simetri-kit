<?php

namespace Database\Seeders;

use App\Models\DemoSeedRecord;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Konten demo untuk showcase ke calon klien (AMC-229, spec 019-demo-content-seeder).
 * Dipanggil HANYA lewat `demo:seed` — tidak pernah lewat DatabaseSeeder/
 * app:setup-client (FR-003). Aman dijalankan berulang: dilewati bila
 * PortfolioCategory sudah pernah di-seed (research.md #4).
 */
class PortfolioDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (DemoSeedRecord::alreadySeeded(PortfolioCategory::class)) {
            return;
        }

        $projects = [
            [
                'category' => 'Residensial',
                'title' => 'Instalasi Atap Rumah Menteng 8.8 kWp',
                'image' => 'images/mockup/home-1.jpg',
                'description' => '<p>Pemasangan panel surya on-grid 8.8 kWp untuk rumah tinggal dua lantai di Menteng, Jakarta Pusat, mengurangi tagihan listrik bulanan hingga 70%.</p>',
                'client_name' => 'Keluarga Wijaya',
            ],
            [
                'category' => 'Komersial & Industri',
                'title' => 'Sistem Atap Pabrik Cikarang 250 kWp',
                'image' => 'images/mockup/home-2.jpg',
                'description' => '<p>Instalasi rooftop solar skala industri untuk fasilitas manufaktur di Cikarang, memangkas beban puncak listrik pabrik secara signifikan.</p>',
                'client_name' => 'PT Manufaktur Sejahtera',
            ],
            [
                'category' => 'Residensial',
                'title' => 'Rumah Tropis Bandung dengan Baterai Backup',
                'image' => 'images/mockup/home-3.jpg',
                'description' => '<p>Sistem hybrid dengan baterai penyimpanan untuk rumah di kawasan Bandung Utara yang sering mengalami pemadaman listrik.</p>',
                'client_name' => 'Bapak Hendra',
            ],
            [
                'category' => 'Komersial & Industri',
                'title' => 'Kantor Pusat Perusahaan Retail Surabaya',
                'image' => 'images/mockup/home-4.jpg',
                'description' => '<p>Panel surya kanopi parkir dan atap gedung kantor pusat, sekaligus menjadi etalase komitmen keberlanjutan perusahaan.</p>',
                'client_name' => 'PT Retail Nusantara',
            ],
        ];

        $categories = collect($projects)->pluck('category')->unique()->values();

        $categoryModels = $categories->mapWithKeys(function (string $name, int $index) {
            $category = PortfolioCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'order' => $index]
            );
            DemoSeedRecord::recordFor($category);

            return [$name => $category];
        });

        foreach ($projects as $index => $project) {
            $portfolioProject = PortfolioProject::create([
                'portfolio_category_id' => $categoryModels[$project['category']]->id,
                'title' => $project['title'],
                'slug' => Str::slug($project['title']).'-'.($index + 1),
                'description' => $project['description'],
                'images' => [$project['image']],
                'client_name' => $project['client_name'],
                'order' => $index,
                'is_active' => true,
            ]);

            DemoSeedRecord::recordFor($portfolioProject);
        }
    }
}
