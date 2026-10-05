<?php

namespace App\Support\PageContent;

use App\Settings\SiteSettings;

/**
 * Isi awal halaman Kebijakan Privasi dan Syarat & Ketentuan, disalin dari desain.
 * Merek dan data perusahaan ditulis sebagai placeholder dan diisi dari Pengaturan Umum saat instalasi;
 * setelah tersimpan, teks sepenuhnya milik admin. Teks hukum ini perlu ditinjau klien sebelum tayang.
 */
class DefaultLegalPages
{
    private const FALLBACK_EMAIL = 'hello@suoer.id';

    private const FALLBACK_PHONE = '(021) 5890-7722';

    private const FALLBACK_ADDRESS = 'Gedung Energi Hijau Lt. 8, Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190';

    /**
     * @return list<array{slug: string, title: string, meta_description: string, legal: array<string, mixed>}>
     */
    public static function pages(): array
    {
        return [self::privacy(), self::terms()];
    }

    /**
     * Halaman dengan placeholder terisi dari Pengaturan Umum (cadangan: nilai pada desain).
     *
     * @param  array{slug: string, title: string, meta_description: string, legal: array<string, mixed>}  $page
     * @return array{slug: string, title: string, meta_description: string, legal: array<string, mixed>}
     */
    public static function forCurrentSite(array $page): array
    {
        $site = app(SiteSettings::class);

        $replacements = [
            '{app_name}' => (string) ($site->site_name ?: config('app.name')),
            '{company_email}' => (string) ($site->company_email ?: self::FALLBACK_EMAIL),
            '{company_phone}' => (string) ($site->company_phone ?: self::FALLBACK_PHONE),
            '{company_address}' => (string) ($site->company_address ?: self::FALLBACK_ADDRESS),
        ];

        $fill = function (mixed $value) use (&$fill, $replacements): mixed {
            if (is_string($value)) {
                return str_replace(array_keys($replacements), array_values($replacements), $value);
            }

            return is_array($value) ? array_map($fill, $value) : $value;
        };

        return $fill($page);
    }

    /**
     * @return array{slug: string, title: string, meta_description: string, legal: array<string, mixed>}
     */
    private static function privacy(): array
    {
        return [
            'slug' => 'kebijakan-privasi',
            'title' => 'Kebijakan Privasi',
            'meta_description' => 'Komitmen {app_name} dalam menjaga transparansi, privasi, dan keamanan data seluruh pelanggan kami.',
            'legal' => [
                'subtitle' => 'Komitmen {app_name} dalam menjaga transparansi, privasi, dan keamanan data seluruh pelanggan kami.',
                'intro' => '<p>Selamat datang di portal kebijakan privasi <strong>{app_name}</strong>. Kami sangat menghargai privasi dan kepercayaan Anda dalam mempercayakan analisis kelayakan PLTS, pengadaan komponen tenaga surya, serta implementasi transisi energi bersih. Kebijakan ini menjelaskan secara transparan bagaimana kami menghimpun, mengamankan, dan mengelola data personal maupun teknis bangunan Anda sesuai dengan peraturan perundang-undangan Republik Indonesia, termasuk UU Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022).</p>',
                'highlight_title' => 'Integritas Perlindungan Data Terstandar',
                'highlight_body' => '{app_name} tidak pernah memperjualbelikan basis data pelanggan, riwayat tagihan listrik PLN, maupun sketsa struktural atap properti kepada pihak komersial eksternal tanpa mandat tertulis Anda. Seluruh data semata-mata dipakai untuk ketepatan teknis sistem fotovoltaik.',
                'sections' => [
                    [
                        'label' => null,
                        'title' => 'Informasi yang Kami Kumpulkan',
                        'body' => '<p>Untuk merancang sistem PLTS On-Grid, Off-Grid, maupun Hybrid yang presisi, {app_name} mengumpulkan beberapa kategori data saat Anda berinteraksi dengan website atau kalkulator surya kami:</p><ul><li><strong>Data Kontak Pelanggan:</strong> Nama lengkap, alamat email aktif, nomor WhatsApp/telepon, serta lokasi geografis atau kota domisili instalasi.</li><li><strong>Data Teknis Konsumsi Daya PLN:</strong> Kapasitas daya terpasang (misal 2.200 VA, 5.500 VA, atau B2B kVA), estimasi rata-rata pengeluaran tagihan listrik per bulan, dan tipe fase listrik.</li><li><strong>Dokumen Pendukung Sukarela:</strong> Salinan foto tagihan rekening listrik PLN (IDPEL) atau foto penampang atap yang diunggah secara sadar guna verifikasi audit kelayakan sinar matahari.</li><li><strong>Informasi Perangkat &amp; Sesi Website:</strong> Alamat IP terselubung, tipe peramban web (browser), preferensi bahasa, dan pola kunjungan halaman demi kenyamanan antarmuka.</li></ul>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => null,
                        'title' => 'Penggunaan Informasi & Data',
                        'body' => '<p>Kami memproses data Anda dengan dasar pemenuhan kewajiban pra-kontraktual dan implementasi proyek energi terbarukan:</p>',
                        'cards' => [
                            ['icon' => 'solar_power', 'title' => 'Analisis Kapasitas Sistem', 'text' => 'Melakukan simulasi produksi kWh harian dan kalkulasi jumlah modul solar panel serta kapasitas inverter {app_name} yang ideal.'],
                            ['icon' => 'request_quote', 'title' => 'Proposal Penawaran Resmi', 'text' => 'Menyusun estimasi Return on Investment (ROI), skema pembiayaan, serta rincian Bill of Quantity (BoQ) yang transparan.'],
                            ['icon' => 'electric_meter', 'title' => 'Perizinan Net Metering PLN', 'text' => 'Mendampingi kepengurusan administratif Sertifikat Laik Operasi (SLO) serta permohonan penggantian meteran ekspor-impor PLN.'],
                            ['icon' => 'engineering', 'title' => 'Dukungan Garansi & O&M', 'text' => 'Registrasi kartu garansi 12 tahun produk dan pemeliharaan performa sistem berkala oleh teknisi {app_name} terakreditasi.'],
                        ],
                        'note' => null,
                    ],
                    [
                        'label' => null,
                        'title' => 'Penyimpanan & Keamanan Data',
                        'body' => '<p>Kami menerapkan standar arsitektur keamanan bertingkat demi melindungi integritas berkas digital Anda:</p>',
                        'cards' => [
                            ['icon' => 'lock', 'title' => 'Enkripsi SSL/TLS 256-Bit', 'text' => 'Seluruh pengiriman formulir simulasi dan unggahan dokumen teknis dilindungi protokol enkripsi kelas perbankan untuk mencegah intersepsi pihak luar.'],
                            ['icon' => 'admin_panel_settings', 'title' => 'Akses Khusus Berbasis Peran (RBAC)', 'text' => 'Akses ke detail identitas dan denah atap dibatasi secara tegas hanya untuk insinyur surya, konsultan teknis, dan staf perizinan proyek yang ditugaskan secara resmi.'],
                        ],
                        'note' => null,
                    ],
                    [
                        'label' => null,
                        'title' => 'Berbagi Informasi dengan Pihak Ketiga',
                        'body' => '<p>{app_name} beroperasi berdasarkan prinsip minimasi pembagian informasi. Data Anda hanya dapat dialirkan ke instansi terkait berikut:</p><ul><li><strong>PT PLN (Persero):</strong> Pengajuan persetujuan interkoneksi jaringan paralel, uji komisioning, dan pemasangan kWh meter Exim (ekspor impor).</li><li><strong>Lembaga Inspeksi Teknik (LIT):</strong> Penerbitan Sertifikat Laik Operasi (SLO) yang diwajibkan oleh Kementerian ESDM Republik Indonesia.</li><li><strong>Mitra Logistik Terpercaya:</strong> Hanya data nama, nomor telepon penerima, dan alamat pengiriman barang untuk pengiriman fisik solar panel, inverter, atau bracket penopang.</li></ul>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => null,
                        'title' => 'Hak dan Kendali Pengguna',
                        'body' => '<p>Sebagai subjek data, Anda memegang kendali penuh atas informasi pribadi Anda sesuai UU PDP. Anda berhak untuk:</p>',
                        'cards' => [
                            ['icon' => 'visibility', 'title' => 'Hak Akses & Salinan', 'text' => 'Meminta salinan data teknis proyek dan berkas yang tersimpan di sistem kami.'],
                            ['icon' => 'edit_note', 'title' => 'Hak Koreksi', 'text' => 'Memperbarui informasi kontak, alamat atap, atau detail daya yang mengalami perubahan.'],
                            ['icon' => 'delete_sweep', 'title' => 'Hak Penghapusan Data', 'text' => 'Mengajukan penghapusan catatan histori simulasi apabila Anda membatalkan rencana instalasi.'],
                        ],
                        'note' => null,
                    ],
                    [
                        'label' => null,
                        'title' => 'Cookie & Teknologi Pelacakan',
                        'body' => '<p>Kami menggunakan cookie esensial dan analitik agregat untuk memastikan kalkulator surya interaktif berjalan optimal di setiap jenis layar gawai. Anda dapat menonaktifkan cookie melalui pengaturan peramban masing-masing tanpa membatasi akses membaca katalog produk kami.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => null,
                        'title' => 'Hubungi Petugas Privasi Data',
                        'body' => '<p>Jika ada pertanyaan, tanggapan, atau permohonan pelaksanaan hak privasi Anda, silakan hubungi tim kepatuhan kami melalui kanal resmi:</p><p><strong>Data Protection Officer (DPO) {app_name}</strong></p><p>{company_address}</p><p>Surel Khusus Privasi: <a href="mailto:{company_email}">{company_email}</a></p><p>Layanan Telepon: {company_phone} (Hari kerja 08.30 – 17.00 WIB)</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                ],
                'contact_title' => 'Punya pertanyaan seputar data Anda?',
                'contact_text' => 'Tim Data Protection Officer (DPO) kami siap memberikan klarifikasi hak privasi dan perlindungan data Anda.',
                'contact_whatsapp_label' => 'Konsultasi DPO via WhatsApp',
                'contact_whatsapp_message' => 'Halo {app_name}, saya ingin bertanya seputar kebijakan privasi dan data saya.',
                'contact_email' => '{company_email}',
                'pdf_path' => null,
                'pdf_label' => 'Unduh Salinan Kebijakan (PDF)',
                'cta_title' => null,
                'cta_body' => null,
                'cta_button_label' => null,
                'cta_button_url' => null,
            ],
        ];
    }

    /**
     * @return array{slug: string, title: string, meta_description: string, legal: array<string, mixed>}
     */
    private static function terms(): array
    {
        return [
            'slug' => 'syarat-ketentuan',
            'title' => 'Syarat & Ketentuan',
            'meta_description' => 'Ketentuan layanan, pedoman garansi produk, standar instalasi teknis, dan hak kewajiban penggunaan layanan {app_name}.',
            'legal' => [
                'subtitle' => 'Ketentuan layanan, pedoman garansi produk, standar instalasi teknis, dan hak kewajiban penggunaan layanan {app_name} Solar Energy.',
                'intro' => null,
                'highlight_title' => 'Jaminan Produk 100% Original & Bergaransi Resmi',
                'highlight_body' => 'Seluruh unit Photovoltaic (PV), Solar Inverter Hybrid, BESS (Battery Energy Storage System), dan proteksi sirkuit DC/AC yang disuplai oleh {app_name} Solar Energy Indonesia terjamin memiliki nomor seri pabrik terdaftar, lulus sertifikasi SNI/IEC, dan didukung garansi resmi distributor tunggal.',
                'sections' => [
                    [
                        'label' => 'Pendahuluan',
                        'title' => 'Ketentuan Umum & Penerimaan Layanan',
                        'body' => '<p>Dengan mengakses situs web, meminta proposal teknis, maupun memesan paket instalasi PLTS (Pembangkit Listrik Tenaga Surya) melalui PT {app_name} Solar Energy Indonesia ("{app_name}"), Pengguna ("Pelanggan", baik individu perorangan maupun entitas badan hukum) menyetujui untuk terikat secara sah oleh seluruh Syarat &amp; Ketentuan ini.</p><p>{app_name} berhak memodifikasi, menambah, atau merevisi dokumen ketentuan ini sewaktu-waktu sesuai dengan pembaruan regulasi ketenagalistrikan Kementerian ESDM atau kebijakan teknis PLN tanpa pemberitahuan individual sebelumnya. Penggunaan berkelanjutan atas platform dan layanan menandakan persetujuan Anda terhadap perubahan tersebut.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => 'Kalkulator & Analisis',
                        'title' => 'Layanan Konsultasi & Estimasi Kalkulator',
                        'body' => '<p>Simulator penghematan listrik, kalkulator kapasitas kWp, dan estimasi ROI (Return on Investment) yang terdapat pada portal digital {app_name} merupakan simulasi indikatif berdasar rata-rata radiasi insolasi matahari wilayah Indonesia (irradiance 4.5 – 5.2 kWh/m²/hari) dan tarif dasar listrik PLN terkini.</p><p>Hasil kalkulator bukan merupakan penawaran mengikat secara finansial. Angka produksi energi aktual, kapasitas modul terpasang, dan spesifikasi inverter definitif hanya akan dirilis melalui <em>Engineering Design Document</em> resmi setelah tim teknisi {app_name} melakukan survei fisik terhadap kemiringan atap, struktur load-bearing, potensi shading (bayangan pohon/gedung), dan kondisi instalasi eksisting.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => 'Transaksi & Kontrak',
                        'title' => 'Pemesanan, Kontrak & Pembayaran',
                        'body' => '<p>Proses pengadaan sistem PLTS dituangkan dalam Surat Kontrak Kerja Sama (SPK) yang memuat rincian Bill of Quantity (BoQ). Skema termin pembayaran standar mengacu pada protokol:</p><ul><li><strong>Termin 1 (Uang Muka / DP):</strong> 30% pada saat penandatanganan SPK dan konfirmasi jadwal pengerjaan.</li><li><strong>Termin 2 (Mobilisasi Material):</strong> 50% setelah seluruh panel, inverter, mounting bracket, dan kabel tiba di lokasi proyek.</li><li><strong>Termin 3 (Pelunasan &amp; COD):</strong> 20% setelah pengujian Commissioning (Testing &amp; Commissioning) selesai dan penandatanganan Berita Acara Serah Terima (BAST).</li></ul>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => 'Keselamatan & Eksekusi',
                        'title' => 'Standar Instalasi & Keselamatan Kerja (K3)',
                        'body' => '<p>{app_name} menerapkan standar Health, Safety, and Environment (HSE) bertaraf internasional. Seluruh teknisi atap kami dibekali lisensi Bekerja Pada Ketinggian (TKPK) dan sertifikasi instalasi kelistrikan tegangan DC/AC.</p><p>Pelanggan berkewajiban memberikan izin akses aman menuju area atap, titik panel distribusi utama (MDB), serta memastikan ketersediaan sumber air dan listrik kerja darurat selama periode perakitan sistem berlangsung.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => 'Jaminan Kualitas',
                        'title' => 'Garansi Produk & Pemeliharaan (O&M)',
                        'body' => '<p>Kami memberikan perlindungan investasi energi surya Anda dengan skema garansi bertingkat yang mencakup:</p>',
                        'cards' => [
                            ['icon' => 'workspace_premium', 'title' => '25 Tahun — Garansi Performa Panel', 'text' => 'Output degradasi daya dijamin min. 84.8% pada tahun ke-25.'],
                            ['icon' => 'bolt', 'title' => '5 - 10 Tahun — Garansi Inverter {app_name}', 'text' => 'Penggantian unit atau suku cadang orisinil bebas biaya suku cadang.'],
                            ['icon' => 'construction', 'title' => '1 - 3 Tahun — Workmanship Atap', 'text' => 'Jaminan anti-kebocoran pada titik penetrasi atap pasca instalasi.'],
                        ],
                        'note' => '*Garansi gugur jika ditemukan modifikasi rangkaian kabel oleh pihak ketiga tanpa persetujuan tertulis {app_name}, pemakaian beban berlebih (overload) di luar rating kapasitas inverter, atau kerusakan mekanis disengaja.',
                    ],
                    [
                        'label' => 'Legalitas Grid',
                        'title' => 'Perizinan PLN, Sertifikasi SLO & Net Metering',
                        'body' => '<p>Untuk sistem On-Grid dan Hybrid yang terhubung jaringan PT PLN (Persero), {app_name} mendampingi dan memfasilitasi proses uji kelaikan:</p><ul><li>Penerbitan Sertifikat Laik Operasi (SLO) melalui Lembaga Inspeksi Teknis (LIT) terakreditasi Kementerian ESDM.</li><li>Pengajuan izin paralel jaringan ke kantor unit pelayanan PLN setempat.</li><li>Penggantian KWH meter reguler menjadi KWH Meter Ekspor-Impor (Exim) dua arah.</li></ul><p>Durasi pemasangan meteran Exim sepenuhnya tunduk pada ketersediaan alokasi kuota sistem PLTS Atap dan jadwal operasional Rayon PLN setempat.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => 'Kewajiban Hukum',
                        'title' => 'Batasan Tanggung Jawab & Force Majeure',
                        'body' => '<p>{app_name} tidak bertanggung jawab atas kegagalan kinerja pembangkitan atau keterlambatan instalasi yang disebabkan oleh Keadaan Kahar (<em>Force Majeure</em>), meliputi namun tidak terbatas pada: gempa bumi berkekuatan tinggi, angin puting beliung, kebakaran atap properti di luar perimeter PV, petir langsung dengan kekuatan di luar batas proteksi Surge Protection Device (SPD) standar IEEE, huru-hara sipil, atau perubahan drastis regulasi kuota kuantitatif nasional.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                    [
                        'label' => 'Hukum Indonesia',
                        'title' => 'Hukum yang Berlaku & Penyelesaian Sengketa',
                        'body' => '<p>Syarat &amp; Ketentuan ini diatur dan ditafsirkan berdasarkan hukum Negara Kesatuan Republik Indonesia. Setiap perselisihan, sengketa, atau klaim yang timbul sehubungan dengan pelaksanaan layanan ini wajib diselesaikan secara musyawarah untuk mufakat dalam tempo 30 (tiga puluh) hari kalender.</p><p>Apabila musyawarah tidak mencapai mufakat, para pihak sepakat untuk memilih tempat kedudukan hukum yang tetap dan tidak berubah di Kepaniteraan Pengadilan Negeri Jakarta Selatan.</p>',
                        'cards' => [],
                        'note' => null,
                    ],
                ],
                'contact_title' => 'Konsultasi Teknis',
                'contact_text' => 'Perlu klarifikasi khusus mengenai klausul garansi atau survei instalasi atap komersial? Tim hukum & teknis kami siap membantu Anda.',
                'contact_whatsapp_label' => 'WhatsApp Resmi {app_name}',
                'contact_whatsapp_message' => 'Halo {app_name}, saya ingin bertanya seputar syarat dan ketentuan layanan.',
                'contact_email' => null,
                'pdf_path' => null,
                'pdf_label' => 'Unduh Syarat & Ketentuan (PDF)',
                'cta_title' => null,
                'cta_body' => null,
                'cta_button_label' => null,
                'cta_button_url' => null,
            ],
        ];
    }
}
