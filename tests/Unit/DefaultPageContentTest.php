<?php

namespace Tests\Unit;

use App\Enums\CtaPlacement;
use App\Enums\PageSection;
use App\Support\MaterialSymbolsIcons;
use App\Support\PageContent\DefaultPageContent;
use PHPUnit\Framework\TestCase;

/**
 * Nilai bawaan wajib sama persis dengan teks lama di Blade; satu-satunya perbedaan `SUOER` → `{app_name}`,
 * yang diisi Nama Situs sekali saat instalasi (SC-006).
 */
class DefaultPageContentTest extends TestCase
{
    public function test_headings_match_legacy_text(): void
    {
        $this->assertSame([
            'beranda.mengapa-beralih' => [
                'title' => "Mengapa Beralih\nBersama {app_name}?",
                'subtitle' => 'Investasi cerdas untuk masa depan, dirancang dengan presisi tinggi khusus kondisi iklim Indonesia.',
            ],
            'beranda.cara-kerja' => [
                'title' => 'Sederhana dan Mulus',
                'subtitle' => 'Bagaimana cahaya matahari bertransformasi menjadi energi andal untuk rumah dan bisnis Anda.',
            ],
            'karir.mengapa-bergabung' => [
                'title' => 'Mengapa Bergabung dengan Kami?',
                'subtitle' => 'Budaya kerja yang mendukung pertumbuhan dan inovasi Anda.',
            ],
            'karir.proses-rekrutmen' => [
                'title' => 'Proses Rekrutmen',
                'subtitle' => null,
            ],
            'tentang-kami.misi' => [
                'title' => 'Bagaimana Kami Mewujudkannya',
                'subtitle' => 'Langkah konkret kami dalam menghadirkan ekosistem energi surya terpadu, presisi, dan berkelanjutan untuk Indonesia.',
                'eyebrow' => 'Misi',
            ],
            'tentang-kami.nilai' => [
                'title' => 'Nilai-Nilai Kami',
                'subtitle' => 'Fondasi dan komitmen kami dalam melayani pelanggan dan menjaga kelestarian bumi.',
                'featured_image_path' => null,
                'featured_icon' => 'eco',
                'featured_title' => 'Ekonomi Hijau & Lapangan Kerja',
                'featured_description' => 'Kami tidak hanya membangun infrastruktur energi, tetapi juga menggerakkan roda ekonomi hijau dengan menciptakan lapangan kerja baru bagi tenaga kerja lokal.',
            ],
            'tentang-kami.trust-strip' => [
                'title' => 'Trust Strip',
                'subtitle' => null,
            ],
        ], DefaultPageContent::headings());
    }

    public function test_items_match_legacy_text(): void
    {
        $items = DefaultPageContent::items();

        $this->assertSame([
            ['savings', 'Efisien & Terjangkau', 'Turunkan tagihan listrik hingga 80% dengan panel efisiensi tinggi berteknologi monokristalin terbaru.', false],
            ['verified', 'Garansi Panjang', 'Ketenangan pikiran dengan garansi performa panel hingga 25 tahun dan garansi pengerjaan profesional.', true],
            ['eco', 'Ramah Lingkungan', 'Kurangi jejak karbon Anda. Satu instalasi setara dengan menanam puluhan pohon setiap tahunnya.', false],
        ], $this->flatten($items[PageSection::WhyChoose->value]));

        $this->assertSame([
            [null, 'Panel & PV Cell', 'Menyerap sinar matahari dan mengubahnya menjadi energi listrik searah (DC).', false],
            [null, 'DC Power', 'Aliran listrik DC mengalir aman melalui kabel khusus menuju inverter utama.', false],
            [null, 'Inverter', 'Jantung sistem. Mengubah arus DC menjadi arus bolak-balik (AC) untuk alat elektronik.', true],
            [null, 'Storage / Grid', 'Energi digunakan langsung, disimpan di baterai, atau diekspor ke PLN (net-metering).', false],
        ], $this->flatten($items[PageSection::HowItWorks->value]));

        $this->assertSame([
            ['lightbulb', 'Inovasi Berkelanjutan', 'Kami selalu mencari cara baru untuk memaksimalkan efisiensi energi surya dan meminimalkan dampak lingkungan.', false],
            ['groups', 'Kolaborasi Tim', 'Lingkungan kerja yang inklusif di mana setiap ide didengar dan kolaborasi lintas disiplin didorong.', false],
            ['public', 'Dampak Nyata', 'Pekerjaan Anda secara langsung berkontribusi pada pengurangan emisi karbon dan menciptakan masa depan yang lebih hijau.', false],
        ], $this->flatten($items[PageSection::CareerValues->value]));

        $this->assertSame([
            [null, 'Lamar', 'Kirimkan CV dan portofolio Anda melalui portal karir kami.', false],
            [null, 'Wawancara HR', 'Sesi perkenalan untuk menilai kecocokan budaya dan pengalaman dasar.', false],
            [null, 'Penilaian Teknis', 'Wawancara mendalam dengan tim terkait atau studi kasus.', false],
            [null, 'Penawaran', 'Selamat datang di tim! Persiapan onboarding dimulai.', false],
        ], $this->flatten($items[PageSection::RecruitmentProcess->value]));
    }

    public function test_about_items_and_blocks_match_legacy_settings_defaults(): void
    {
        $items = DefaultPageContent::items();

        $this->assertSame(
            ['Solusi Premium & Teruji', 'Pemasangan Presisi', 'Dukungan Purna Jual', 'Edukasi Berkelanjutan', 'Inovasi Teknologi'],
            array_column($items['tentang-kami.misi'], 'title'),
        );
        $this->assertSame(
            [['savings', 'Efisien & Terjangkau', 'Menghadirkan solusi energi yang menekan biaya operasional jangka panjang.'], ['school', 'Edukasi Masyarakat', 'Memberikan pemahaman mendalam tentang transisi energi terbarukan.'], ['handshake', 'Kolaborasi & Infrastruktur', 'Membangun ekosistem bersama mitra strategis untuk jangkauan luas.']],
            array_map(fn (array $i): array => [$i['icon'], $i['title'], $i['description']], $items['tentang-kami.nilai']),
        );
        $this->assertSame(
            [['group', '5.000+', 'Pelanggan Puas'], ['solar_power', '10+ MW', 'Total Kapasitas Terpasang'], ['calendar_month', '15+ Tahun', 'Pengalaman Industri']],
            array_map(fn (array $i): array => [$i['icon'], $i['title'], $i['description']], $items['tentang-kami.trust-strip']),
        );

        $blocks = DefaultPageContent::blocks();

        $this->assertSame('Bagian dari Sinar Mas Elektrindo', $blocks['tentang-kami.siapa-kami']['badge_text']);
        $this->assertSame('Sebagai bagian dari <strong>PT Sinar Mas Elektrindo</strong>, {app_name} hadir membawa komitmen kuat dalam menghadirkan solusi energi surya yang inovatif, efisien, dan andal. Kami memadukan kekuatan infrastruktur global dengan pemahaman mendalam tentang kebutuhan lokal Indonesia.', $blocks['tentang-kami.siapa-kami']['body']);
        $this->assertSame('Visi Kami', $blocks['tentang-kami.visi']['eyebrow']);
        $this->assertSame('Senin - Jumat, 09:00 - 17:00 WIB', $blocks['kontak.info-kontak']['operating_hours']);
        $this->assertSame('Halo, saya ingin konsultasi tentang solusi tenaga surya {app_name}.', $blocks['kontak.info-kontak']['whatsapp_message']);
    }

    public function test_ctas_match_legacy_text_for_every_placement(): void
    {
        $ctas = DefaultPageContent::ctas();

        $this->assertEqualsCanonicalizing(
            array_map(fn (CtaPlacement $placement): string => $placement->value, CtaPlacement::cases()),
            array_keys($ctas),
        );

        $this->assertSame(
            ["Siap beralih ke\nenergi matahari?", 'Mulai perjalanan hijau Anda hari ini. Tim ahli kami siap membantu menganalisa kebutuhan dan memberikan desain sistem gratis.', 'Chat via WhatsApp', 'Isi Form Online'],
            array_values($ctas['beranda']),
        );
        $this->assertSame(
            ['Bingung pilih yang mana?', 'Gunakan kalkulator kami untuk memperkirakan kebutuhan daya dan potensi penghematan bulanan Anda.', 'Coba kalkulator hemat listrik', null],
            array_values($ctas['produk-kalkulator']),
        );
        $this->assertSame(
            ['Belum yakin kapasitas yang Anda butuhkan?', 'Konsultasi gratis dengan tim teknis ahli kami untuk mendapatkan perhitungan yang akurat dan solusi yang tepat.', 'Konsultasi gratis dengan tim {app_name}', null],
            array_values($ctas['produk-penutup']),
        );
        $this->assertSame(
            ['Masa Depan Energi Anda', 'Berinvestasi pada {produk} bukan sekadar mengurangi tagihan listrik, tetapi juga bentuk komitmen terhadap kelestarian bumi — dirancang untuk integrasi mulus dengan arsitektur modern.', 'Konsultasi kebutuhan Anda', null],
            array_values($ctas['produk-detail']),
        );
        $this->assertSame(
            ['Punya pertanyaan seputar energi surya?', 'Tim kami siap membantu — dari pemilihan produk hingga estimasi penghematan untuk rumah atau bisnis Anda.', 'Konsultasi Gratis', null],
            array_values($ctas['artikel-daftar']),
        );
        $this->assertSame(
            ['Siap beralih ke energi surya?', 'Konsultasi gratis dengan tim ahli kami', 'Hubungi via WhatsApp', null],
            array_values($ctas['artikel-detail']),
        );
        $this->assertSame(
            ['Ingin tahu lebih lanjut tentang {app_name}?', 'Ngobrol langsung dengan tim kami', 'Hubungi via WhatsApp', null],
            array_values($ctas['tentang-kami']),
        );
        $this->assertSame(
            ['Masih ada pertanyaan lain?', 'Tim kami siap membantu menjawab kebutuhan spesifik Anda', 'Hubungi Kami', null],
            array_values($ctas['faq']),
        );
        $this->assertSame(
            ['Tidak menemukan posisi yang cocok?', 'Kirimkan CV Anda — kami hubungi saat ada posisi sesuai', 'Hubungi Kami', null],
            array_values($ctas['karir']),
        );
    }

    public function test_default_content_never_contains_client_brand_literal(): void
    {
        $serialized = json_encode([
            DefaultPageContent::headings(),
            DefaultPageContent::items(),
            DefaultPageContent::ctas(),
            DefaultPageContent::blocks(),
        ], JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsString('SUOER', $serialized);
    }

    public function test_every_default_icon_is_selectable(): void
    {
        foreach (DefaultPageContent::items() as $items) {
            foreach ($items as $item) {
                if ($item['icon'] !== null) {
                    $this->assertContains($item['icon'], MaterialSymbolsIcons::keys());
                }
            }
        }
    }

    /**
     * @param  list<array{icon: ?string, title: string, description: string, is_emphasized: bool}>  $items
     * @return list<array{0: ?string, 1: string, 2: string, 3: bool}>
     */
    private function flatten(array $items): array
    {
        return array_map(fn (array $item): array => [$item['icon'], $item['title'], $item['description'], $item['is_emphasized']], $items);
    }
}
