<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\DemoSeedRecord;
use App\Support\ImageUploads;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Banner contoh untuk showcase ke calon klien, kini berisi slide hero
 * lengkap — badge, judul, subjudul, kedua CTA, trust bar, dan preset
 * tampilan — sehingga instalasi baru langsung menampilkan hero utuh alih-alih
 * gambar polos (022-banner-hero-slider, tasks.md T050).
 *
 * Dipanggil HANYA lewat `demo:seed` (AMC-229, spec 019-demo-content-seeder)
 * — tidak lagi lewat DatabaseSeeder/app:setup-client (FR-003). Dilewati bila
 * sudah pernah di-seed sebelumnya (research.md #4).
 */
class BannerSeeder extends Seeder
{
    public function run(): void
    {
        if (DemoSeedRecord::alreadySeeded(Banner::class)) {
            return;
        }

        $slides = [
            [
                'title' => 'Hero — Energi Matahari',
                'image' => 'home-1.jpg',
                'alt_text' => 'Instalasi solar panel rooftop modern di kompleks hunian',
                'badge_text' => 'Solar Panel Terpercaya • Efisiensi Hingga 80%',
                'heading' => 'Nyalakan Rumah & Bisnis Anda dengan Energi Matahari',
                'subheading' => 'Solusi tata surya terdepan untuk efisiensi maksimal dan investasi jangka panjang tanpa mengorbankan estetika hunian Anda.',
                'cta_primary_label' => 'Konsultasi Gratis',
                'cta_primary_url' => '/kontak',
                'cta_secondary_label' => 'Pelajari Cara Kerja',
                'cta_secondary_url' => '/#kalkulator',
                'trust_html' => '<div class="flex items-center gap-2"><span>✓</span><span>★</span><span>✦</span></div><p><strong>500+ Pelanggan Puas</strong></p><p><small>Terpasang di seluruh wilayah Indonesia</small></p>',
                'overlay_style' => 'dark',
                'text_position' => 'left',
            ],
            [
                'title' => 'Promo — Instalasi Bisnis',
                'image' => 'home-2.jpg',
                'alt_text' => 'Panel surya terpasang di atap gedung komersial',
                'badge_text' => 'Untuk Pelaku Usaha',
                'heading' => 'Turunkan Biaya Operasional dengan PLTS Atap',
                'subheading' => 'Desain sistem gratis dan simulasi hemat biaya khusus untuk kebutuhan industri dan komersial.',
                'cta_primary_label' => 'Ambil Penawaran',
                'cta_primary_url' => '/kontak',
                'overlay_style' => 'dark',
                'text_position' => 'center',
            ],
            [
                'title' => 'Galeri — Proyek Terpasang',
                'image' => 'home-3.jpg',
                'alt_text' => 'Detail panel surya monocrystalline close-up',
                'overlay_style' => 'none',
                'text_position' => 'left',
            ],
        ];

        foreach ($slides as $order => $slide) {
            $imagePath = ImageUploads::storeAsWebp(
                new UploadedFile(public_path('images/mockup/'.$slide['image']), $slide['image'], null, null, true),
                'banners',
                maxWidth: 1600,
            );

            $banner = Banner::create([
                'title' => $slide['title'],
                'image_path' => $imagePath,
                'alt_text' => $slide['alt_text'],
                'order' => $order + 1,
                'is_active' => true,
                'badge_text' => $slide['badge_text'] ?? null,
                'heading' => $slide['heading'] ?? null,
                'subheading' => $slide['subheading'] ?? null,
                'cta_primary_label' => $slide['cta_primary_label'] ?? null,
                'cta_primary_url' => $slide['cta_primary_url'] ?? null,
                'cta_secondary_label' => $slide['cta_secondary_label'] ?? null,
                'cta_secondary_url' => $slide['cta_secondary_url'] ?? null,
                'trust_html' => $slide['trust_html'] ?? null,
                'overlay_style' => $slide['overlay_style'],
                'text_position' => $slide['text_position'],
            ]);

            DemoSeedRecord::recordFor($banner);
        }
    }
}
