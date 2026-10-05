<?php

namespace App\Support\PageContent;

use App\Enums\CtaPlacement;
use App\Enums\FaqPlacement;
use App\Enums\PageBlockType;
use App\Enums\PageSection;
use App\Settings\SiteSettings;

/**
 * Nilai bawaan section & CTA, disalin persis dari Blade sebelum konten dipindah ke database.
 * Merek klien ditulis `{app_name}` (constitution Principle I) dan diisi Nama Situs sekali saat
 * instalasi lewat forCurrentSite(); setelah tersimpan, teks sepenuhnya milik admin. `\n` = `<br>`.
 */
class DefaultPageContent
{
    /**
     * @template T of array<string, mixed>
     *
     * @param  T  $values
     * @return T
     */
    public static function forCurrentSite(array $values): array
    {
        $siteName = (string) (app(SiteSettings::class)->site_name ?: config('app.name'));

        return array_map(
            fn (mixed $value): mixed => is_string($value) ? str_replace('{app_name}', $siteName, $value) : $value,
            $values,
        );
    }

    /**
     * @return array<string, array<string, ?string>>
     */
    public static function headings(): array
    {
        return [
            PageSection::WhyChoose->value => [
                'title' => "Mengapa Beralih\nBersama {app_name}?",
                'subtitle' => 'Investasi cerdas untuk masa depan, dirancang dengan presisi tinggi khusus kondisi iklim Indonesia.',
            ],
            PageSection::HowItWorks->value => [
                'title' => 'Sederhana dan Mulus',
                'subtitle' => 'Bagaimana cahaya matahari bertransformasi menjadi energi andal untuk rumah dan bisnis Anda.',
            ],
            PageSection::CareerValues->value => [
                'title' => 'Mengapa Bergabung dengan Kami?',
                'subtitle' => 'Budaya kerja yang mendukung pertumbuhan dan inovasi Anda.',
            ],
            PageSection::RecruitmentProcess->value => [
                'title' => 'Proses Rekrutmen',
                'subtitle' => null,
            ],
            PageSection::AboutMission->value => [
                'title' => 'Bagaimana Kami Mewujudkannya',
                'subtitle' => 'Langkah konkret kami dalam menghadirkan ekosistem energi surya terpadu, presisi, dan berkelanjutan untuk Indonesia.',
                'eyebrow' => 'Misi',
            ],
            PageSection::AboutValues->value => [
                'title' => 'Nilai-Nilai Kami',
                'subtitle' => 'Fondasi dan komitmen kami dalam melayani pelanggan dan menjaga kelestarian bumi.',
                'featured_image_path' => null,
                'featured_icon' => 'eco',
                'featured_title' => 'Ekonomi Hijau & Lapangan Kerja',
                'featured_description' => 'Kami tidak hanya membangun infrastruktur energi, tetapi juga menggerakkan roda ekonomi hijau dengan menciptakan lapangan kerja baru bagi tenaga kerja lokal.',
            ],
            PageSection::AboutTrust->value => [
                'title' => 'Trust Strip',
                'subtitle' => null,
            ],
        ];
    }

    /**
     * @return array<string, list<array{icon: ?string, title: string, description: string, is_emphasized: bool}>>
     */
    public static function items(): array
    {
        return [
            PageSection::WhyChoose->value => [
                ['icon' => 'savings', 'title' => 'Efisien & Terjangkau', 'description' => 'Turunkan tagihan listrik hingga 80% dengan panel efisiensi tinggi berteknologi monokristalin terbaru.', 'is_emphasized' => false],
                ['icon' => 'verified', 'title' => 'Garansi Panjang', 'description' => 'Ketenangan pikiran dengan garansi performa panel hingga 25 tahun dan garansi pengerjaan profesional.', 'is_emphasized' => true],
                ['icon' => 'eco', 'title' => 'Ramah Lingkungan', 'description' => 'Kurangi jejak karbon Anda. Satu instalasi setara dengan menanam puluhan pohon setiap tahunnya.', 'is_emphasized' => false],
            ],
            PageSection::HowItWorks->value => [
                ['icon' => null, 'title' => 'Panel & PV Cell', 'description' => 'Menyerap sinar matahari dan mengubahnya menjadi energi listrik searah (DC).', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'DC Power', 'description' => 'Aliran listrik DC mengalir aman melalui kabel khusus menuju inverter utama.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Inverter', 'description' => 'Jantung sistem. Mengubah arus DC menjadi arus bolak-balik (AC) untuk alat elektronik.', 'is_emphasized' => true],
                ['icon' => null, 'title' => 'Storage / Grid', 'description' => 'Energi digunakan langsung, disimpan di baterai, atau diekspor ke PLN (net-metering).', 'is_emphasized' => false],
            ],
            PageSection::CareerValues->value => [
                ['icon' => 'lightbulb', 'title' => 'Inovasi Berkelanjutan', 'description' => 'Kami selalu mencari cara baru untuk memaksimalkan efisiensi energi surya dan meminimalkan dampak lingkungan.', 'is_emphasized' => false],
                ['icon' => 'groups', 'title' => 'Kolaborasi Tim', 'description' => 'Lingkungan kerja yang inklusif di mana setiap ide didengar dan kolaborasi lintas disiplin didorong.', 'is_emphasized' => false],
                ['icon' => 'public', 'title' => 'Dampak Nyata', 'description' => 'Pekerjaan Anda secara langsung berkontribusi pada pengurangan emisi karbon dan menciptakan masa depan yang lebih hijau.', 'is_emphasized' => false],
            ],
            PageSection::RecruitmentProcess->value => [
                ['icon' => null, 'title' => 'Lamar', 'description' => 'Kirimkan CV dan portofolio Anda melalui portal karir kami.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Wawancara HR', 'description' => 'Sesi perkenalan untuk menilai kecocokan budaya dan pengalaman dasar.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Penilaian Teknis', 'description' => 'Wawancara mendalam dengan tim terkait atau studi kasus.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Penawaran', 'description' => 'Selamat datang di tim! Persiapan onboarding dimulai.', 'is_emphasized' => false],
            ],
            PageSection::AboutMission->value => [
                ['icon' => null, 'title' => 'Solusi Premium & Teruji', 'description' => 'Menyediakan panel surya dan inverter berkualitas terbaik yang telah teruji secara global untuk performa maksimal di iklim tropis.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Pemasangan Presisi', 'description' => 'Menjamin instalasi yang aman, rapi, dan efisien oleh tim teknisi bersertifikat yang memahami standar kelistrikan nasional.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Dukungan Purna Jual', 'description' => 'Memberikan ketenangan pikiran melalui pemeliharaan responsif dan garansi performa jangka panjang yang dapat diandalkan.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Edukasi Berkelanjutan', 'description' => 'Meningkatkan kesadaran masyarakat tentang manfaat dan pentingnya beralih ke energi bersih.', 'is_emphasized' => false],
                ['icon' => null, 'title' => 'Inovasi Teknologi', 'description' => 'Terus mengadopsi teknologi terbaru dalam penyimpanan dan manajemen energi untuk efisiensi yang lebih baik.', 'is_emphasized' => false],
            ],
            PageSection::AboutValues->value => [
                ['icon' => 'savings', 'title' => 'Efisien & Terjangkau', 'description' => 'Menghadirkan solusi energi yang menekan biaya operasional jangka panjang.', 'is_emphasized' => false],
                ['icon' => 'school', 'title' => 'Edukasi Masyarakat', 'description' => 'Memberikan pemahaman mendalam tentang transisi energi terbarukan.', 'is_emphasized' => false],
                ['icon' => 'handshake', 'title' => 'Kolaborasi & Infrastruktur', 'description' => 'Membangun ekosistem bersama mitra strategis untuk jangkauan luas.', 'is_emphasized' => false],
            ],
            PageSection::AboutTrust->value => [
                ['icon' => 'group', 'title' => '5.000+', 'description' => 'Pelanggan Puas', 'is_emphasized' => false],
                ['icon' => 'solar_power', 'title' => '10+ MW', 'description' => 'Total Kapasitas Terpasang', 'is_emphasized' => false],
                ['icon' => 'calendar_month', 'title' => '15+ Tahun', 'description' => 'Pengalaman Industri', 'is_emphasized' => false],
            ],
        ];
    }

    /**
     * Isi bawaan blok halaman (kunci `data`).
     *
     * @return array<string, array<string, ?string>>
     */
    public static function blocks(): array
    {
        return [
            PageBlockType::AboutHero->value => [
                'image_path' => null,
                'title' => 'Mengenal {app_name} Lebih Dekat',
                'subtitle' => 'Menghadirkan solusi energi surya inovatif dan berkelanjutan untuk masa depan Indonesia yang lebih cerah.',
            ],
            PageBlockType::AboutWhoWeAre->value => [
                'image_path' => null,
                'badge_text' => 'Bagian dari Sinar Mas Elektrindo',
                'eyebrow' => 'Tentang Kami',
                'heading' => 'Menghadirkan Energi Surya Andal & Terpercaya untuk Indonesia',
                'body' => 'Sebagai bagian dari <strong>PT Sinar Mas Elektrindo</strong>, {app_name} hadir membawa komitmen kuat dalam menghadirkan solusi energi surya yang inovatif, efisien, dan andal. Kami memadukan kekuatan infrastruktur global dengan pemahaman mendalam tentang kebutuhan lokal Indonesia.',
                'quote' => 'Misi kami bukan sekadar menjual panel, tetapi menjadi <span class="text-secondary">mitra transformasi energi</span> yang memberdayakan masyarakat dan bisnis menuju masa depan yang lebih hijau.',
            ],
            PageBlockType::AboutVision->value => [
                'eyebrow' => 'Visi Kami',
                'heading' => 'Menjadi pelopor energi surya di Asia Tenggara yang paling dipercaya, mendorong masa depan di mana setiap bangunan mandiri energi dan berkelanjutan.',
                'subtext' => 'Membangun ekosistem tenaga surya yang terintegrasi, transparan, dan dapat diakses oleh seluruh lapisan masyarakat.',
            ],
            PageBlockType::ContactInfo->value => [
                'whatsapp_label' => 'Chat via WhatsApp',
                'operating_hours' => 'Senin - Jumat, 09:00 - 17:00 WIB',
                'whatsapp_message' => 'Halo, saya ingin konsultasi tentang solusi tenaga surya {app_name}.',
            ],
            PageBlockType::ProductsHero->value => [
                'image_path' => null,
                'title' => 'Katalog Produk',
                'subtitle' => 'Temukan panel surya dan inverter yang tepat untuk proyek Anda, dari skala rumah tangga hingga industri besar.',
            ],
            PageBlockType::CareerHero->value => [
                'image_path' => null,
                'title' => 'Gabung dengan Revolusi Energi Bersama {app_name}',
                'subtitle' => 'Kami mencari pemikir inovatif dan bersemangat untuk membangun masa depan yang berkelanjutan.',
            ],
            PageBlockType::ArticlesHero->value => [
                'image_path' => null,
                'title' => 'Wawasan & Artikel',
                'subtitle' => 'Temukan pembaruan terkini seputar inovasi energi surya, tips efisiensi pemakaian daya, serta studi kasus instalasi di Indonesia.',
            ],
            PageBlockType::FaqHero->value => [
                'image_path' => null,
                'title' => 'Pertanyaan Umum',
                'subtitle' => 'Temukan jawaban cepat seputar layanan, instalasi, dan produk panel surya kami.',
            ],
            PageBlockType::ContactHero->value => [
                'image_path' => null,
                'title' => 'Mari Wujudkan Rumah Hemat Energi',
                'subtitle' => 'Tim kami siap membantu menjawab pertanyaan dan memberikan konsultasi gratis untuk kebutuhan energi surya Anda.',
            ],
            PageBlockType::PortfolioHero->value => [
                'image_path' => null,
                'title' => 'Portofolio Proyek',
                'subtitle' => 'Dokumentasi instalasi sistem PLTS terpercaya dan solusi energi terbarukan {app_name} untuk hunian modern hingga kawasan industri di seluruh Indonesia.',
            ],
        ];
    }

    /**
     * @return array<string, array{title: string, body: ?string, primary_label: string, secondary_label: ?string}>
     */
    public static function ctas(): array
    {
        return [
            CtaPlacement::Home->value => [
                'title' => "Siap beralih ke\nenergi matahari?",
                'body' => 'Mulai perjalanan hijau Anda hari ini. Tim ahli kami siap membantu menganalisa kebutuhan dan memberikan desain sistem gratis.',
                'primary_label' => 'Chat via WhatsApp',
                'secondary_label' => 'Isi Form Online',
            ],
            CtaPlacement::ProductCalculator->value => [
                'title' => 'Bingung pilih yang mana?',
                'body' => 'Gunakan kalkulator kami untuk memperkirakan kebutuhan daya dan potensi penghematan bulanan Anda.',
                'primary_label' => 'Coba kalkulator hemat listrik',
                'secondary_label' => null,
            ],
            CtaPlacement::ProductClosing->value => [
                'title' => 'Belum yakin kapasitas yang Anda butuhkan?',
                'body' => 'Konsultasi gratis dengan tim teknis ahli kami untuk mendapatkan perhitungan yang akurat dan solusi yang tepat.',
                'primary_label' => 'Konsultasi gratis dengan tim {app_name}',
                'secondary_label' => null,
            ],
            CtaPlacement::ProductDetail->value => [
                'title' => 'Masa Depan Energi Anda',
                'body' => 'Berinvestasi pada {produk} bukan sekadar mengurangi tagihan listrik, tetapi juga bentuk komitmen terhadap kelestarian bumi — dirancang untuk integrasi mulus dengan arsitektur modern.',
                'primary_label' => 'Konsultasi kebutuhan Anda',
                'secondary_label' => null,
            ],
            CtaPlacement::ArticleIndex->value => [
                'title' => 'Punya pertanyaan seputar energi surya?',
                'body' => 'Tim kami siap membantu — dari pemilihan produk hingga estimasi penghematan untuk rumah atau bisnis Anda.',
                'primary_label' => 'Konsultasi Gratis',
                'secondary_label' => null,
            ],
            CtaPlacement::ArticleDetail->value => [
                'title' => 'Siap beralih ke energi surya?',
                'body' => 'Konsultasi gratis dengan tim ahli kami',
                'primary_label' => 'Hubungi via WhatsApp',
                'secondary_label' => null,
            ],
            CtaPlacement::About->value => [
                'title' => 'Ingin tahu lebih lanjut tentang {app_name}?',
                'body' => 'Ngobrol langsung dengan tim kami',
                'primary_label' => 'Hubungi via WhatsApp',
                'secondary_label' => null,
            ],
            CtaPlacement::Faq->value => [
                'title' => 'Masih ada pertanyaan lain?',
                'body' => 'Tim kami siap membantu menjawab kebutuhan spesifik Anda',
                'primary_label' => 'Hubungi Kami',
                'secondary_label' => null,
            ],
            CtaPlacement::Career->value => [
                'title' => 'Tidak menemukan posisi yang cocok?',
                'body' => 'Kirimkan CV Anda — kami hubungi saat ada posisi sesuai',
                'primary_label' => 'Hubungi Kami',
                'secondary_label' => null,
            ],
        ];
    }

    /**
     * FAQ bawaan per tempat tampil: Halaman FAQ (dari data contoh), serta Produk dan Kontak (sebelumnya tertulis di Blade).
     *
     * @return array<string, list<array{question: string, answer: string, category?: string}>>
     */
    public static function faqs(): array
    {
        return [
            FaqPlacement::Faq->value => [
                ['category' => 'Instalasi', 'question' => 'Berapa lama proses instalasi panel surya?', 'answer' => 'Untuk instalasi rumah tangga standar, proses pemasangan biasanya memakan waktu 1-3 hari kerja setelah survei lokasi dan persetujuan desain sistem.'],
                ['category' => 'Produk & Teknologi', 'question' => 'Apakah panel surya bekerja saat mendung atau hujan?', 'answer' => 'Ya, panel surya tetap menghasilkan listrik saat mendung meski dengan output lebih rendah dibanding cuaca cerah. Produksi listrik akan berhenti hanya saat malam hari.'],
                ['category' => 'Biaya & Penghematan', 'question' => 'Berapa besar penghematan tagihan listrik bulanan?', 'answer' => 'Rata-rata pelanggan {app_name} menghemat 50-80% dari tagihan listrik bulanan, tergantung kapasitas sistem dan pola konsumsi listrik rumah tangga.'],
                ['category' => 'Garansi', 'question' => 'Bagaimana dengan garansi produk dan layanan?', 'answer' => 'Panel surya {app_name} dilengkapi garansi performa hingga 25 tahun, garansi produk 10-12 tahun, dan garansi pengerjaan instalasi profesional.'],
                ['category' => 'Perawatan', 'question' => 'Apakah sistem perlu perawatan rutin?', 'answer' => 'Perawatan minimal — cukup pembersihan panel dari debu secara berkala. Tim {app_name} menyediakan layanan monitoring dan maintenance opsional.'],
            ],
            FaqPlacement::Product->value => [
                ['question' => 'Berapa lama garansi panel?', 'answer' => 'Panel surya {app_name} dilengkapi dengan garansi kinerja linier hingga 25 tahun, memastikan efisiensi panel tidak akan turun di bawah 80% dalam kurun waktu tersebut. Inverter biasanya memiliki garansi standar 5 hingga 10 tahun tergantung model.'],
                ['question' => 'Apakah bisa custom kapasitas?', 'answer' => 'Sangat bisa. Kami merancang sistem berdasarkan kebutuhan beban listrik spesifik dan luas atap yang tersedia. Tim teknisi kami akan melakukan survey untuk merancang kapasitas yang paling optimal.'],
                ['question' => 'Bagaimana proses instalasinya?', 'answer' => 'Proses dimulai dari survey lokasi, perancangan sistem, pengajuan izin (jika on-grid), instalasi fisik oleh teknisi bersertifikat kami, hingga tahap commissioning dan serah terima pengoperasian sistem kepada Anda.'],
            ],
            FaqPlacement::Contact->value => [
                ['question' => 'Setelah kirim pesan, apa langkah selanjutnya?', 'answer' => 'Tim ahli energi surya kami akan meninjau pesan Anda dan membalas dalam maksimal 1x24 jam kerja. Kami mengatur diskusi awal via telepon atau video call untuk memahami kebutuhan energi dan kondisi lokasi Anda sebelum menjadwalkan survei teknis.'],
                ['question' => 'Apakah survei lokasi berbayar?', 'answer' => 'Untuk area Jabodetabek, survei lokasi awal gratis. Untuk area di luar Jabodetabek, biaya survei didiskusikan terlebih dahulu dan dapat diakumulasikan ke nilai proyek jika Anda memutuskan menggunakan layanan kami.'],
                ['question' => 'Bisa konsultasi tanpa datang ke kantor?', 'answer' => 'Tentu. Mayoritas konsultasi awal kami dilakukan daring untuk kenyamanan Anda. Kami memakai data satelit awal untuk estimasi kapasitas atap sebelum tim teknis melakukan kunjungan fisik.'],
            ],
        ];
    }
}
