<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Testimoni contoh diambil dari mockup home-page (public/mockup-master).
 * Untuk demo/dev — sumber data utama adalah CRUD admin (AMC-210). Foto sengaja
 * dikosongkan (section publik menampilkan inisial sebagai fallback).
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Bambang Suryono',
                'attribution' => 'Pemilik Rumah • Menteng, Jakarta',
                'rating' => 5,
                'content' => 'Tagihan bulanan rumah kami di Menteng turun drastis dari 5,8 juta menjadi hanya sekitar 1,4 juta rupiah per bulan. Tim SUOER sangat rapi dalam proses pemasangan tanpa merusak estetika genteng rumah sama sekali.',
            ],
            [
                'name' => 'Ir. Hendra Wijaya',
                'attribution' => 'Factory Operations Director • Cikarang',
                'rating' => 5,
                'content' => 'Untuk fasilitas manufaktur kami di Cikarang, efisiensi energi adalah prioritas nomor satu. Sistem on-grid SUOER membantu memangkas beban puncak pabrik dan tim after-sales sangat responsif memberikan laporan pemantauan real-time bulanan.',
            ],
            [
                'name' => 'Ni Luh Ayu Sukma',
                'attribution' => 'Villa & Resort Owner • Ubud, Bali',
                'rating' => 5,
                'content' => 'Mengoperasikan komplek villa dengan AC dan pompa kolam renang yang menyala seharian tadinya sangat membebani operasional. Dengan PLTS Hybrid SUOER, tamu kami senang dengan konsep green living dan biaya listrik kami terpangkas drastis.',
            ],
        ];

        foreach ($testimonials as $order => $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['name' => $testimonial['name']],
                [
                    'attribution' => $testimonial['attribution'],
                    'content' => $testimonial['content'],
                    'rating' => $testimonial['rating'],
                    'photo_path' => null,
                    'order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }
}
