<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

/**
 * Data contoh (seed) dari mockup
 * public/mockup-html/faq_suoer_100_consistent_header_footer.
 */
class FaqItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'question' => 'Berapa lama proses instalasi panel surya?',
                'answer' => 'Untuk instalasi rumah tangga standar, proses pemasangan biasanya memakan waktu 1-3 hari kerja setelah survei lokasi dan persetujuan desain sistem.',
            ],
            [
                'question' => 'Apakah panel surya bekerja saat mendung atau hujan?',
                'answer' => 'Ya, panel surya tetap menghasilkan listrik saat mendung meski dengan output lebih rendah dibanding cuaca cerah. Produksi listrik akan berhenti hanya saat malam hari.',
            ],
            [
                'question' => 'Berapa besar penghematan tagihan listrik bulanan?',
                'answer' => 'Rata-rata pelanggan SUOER menghemat 50-80% dari tagihan listrik bulanan, tergantung kapasitas sistem dan pola konsumsi listrik rumah tangga.',
            ],
            [
                'question' => 'Bagaimana dengan garansi produk dan layanan?',
                'answer' => 'Panel surya SUOER dilengkapi garansi performa hingga 25 tahun, garansi produk 10-12 tahun, dan garansi pengerjaan instalasi profesional.',
            ],
            [
                'question' => 'Apakah sistem perlu perawatan rutin?',
                'answer' => 'Perawatan minimal — cukup pembersihan panel dari debu secara berkala. Tim SUOER menyediakan layanan monitoring dan maintenance opsional.',
            ],
        ];

        foreach ($items as $order => $item) {
            FaqItem::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'category' => 'Umum',
                    'order' => $order,
                ]
            );
        }
    }
}
