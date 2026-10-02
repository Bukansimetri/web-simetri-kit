# Data Model: Kelola Section Konten & CTA dari Panel Admin

**Feature**: `029-why-choose-admin` | **Date**: 2026-09-30

## Enum `App\Enums\PageSection` (string-backed)

| Case | Value | Label admin | Halaman | Maks aktif | Ikon | Penonjolan | Subjudul | Nomor langkah |
|---|---|---|---|---|---|---|---|---|
| `WhyChoose` | `beranda.mengapa-beralih` | Beranda – Mengapa Beralih | Beranda | 3 | wajib | ya (maks 1) | ya | – |
| `HowItWorks` | `beranda.cara-kerja` | Beranda – Cara Kerja | Beranda | 4 | – | ya (maks 1) | ya | `01`, `02`, … |
| `CareerValues` | `karir.mengapa-bergabung` | Karir – Mengapa Bergabung | Karir | 3 | wajib | – | ya | – |
| `RecruitmentProcess` | `karir.proses-rekrutmen` | Karir – Proses Rekrutmen | Karir | 4 | – | – | – | `1`, `2`, … |

Methods: `label(): string`, `maxActiveItems(): int`, `hasIcon(): bool`, `supportsEmphasis(): bool`, `hasSubtitle(): bool`, `stepNumber(int $position): ?string` (posisi mulai 1; `null` untuk section kartu).

## Enum `App\Enums\CtaPlacement` (string-backed)

| Case | Value | Label admin | Halaman | Label kolom body | Tombol kedua | Token `{produk}` |
|---|---|---|---|---|---|---|
| `Home` | `beranda` | Beranda – CTA Penutup | `/` | Paragraf | ya | – |
| `ProductCalculator` | `produk-kalkulator` | Produk – CTA Kalkulator | `/produk` | Paragraf | – | – |
| `ProductClosing` | `produk-penutup` | Produk – CTA Penutup | `/produk` | Paragraf | – | – |
| `ProductDetail` | `produk-detail` | Detail Produk – Masa Depan Energi | `/produk/{slug}` | Paragraf | – | ya |
| `ArticleIndex` | `artikel-daftar` | Daftar Artikel – CTA | `/artikel` | Paragraf | – | – |
| `ArticleDetail` | `artikel-detail` | Detail Artikel – CTA | `/artikel/{slug}` | Subjudul | – | – |
| `About` | `tentang-kami` | Tentang Kami – CTA | `/tentang-kami` | Subjudul | – | – |
| `Faq` | `faq` | FAQ – CTA | `/faq` | Subjudul | – | – |
| `Career` | `karir` | Karir – CTA | `/karir` | Subjudul | – | – |

Methods: `label(): string`, `bodyLabel(): string`, `hasSecondaryButton(): bool`, `supportsProductToken(): bool`.

## Tabel `section_items` → Model `App\Models\SectionItem`

| Kolom | Tipe | Aturan |
|---|---|---|
| `id` | bigint PK | |
| `section` | string(64), index | cast `PageSection`; diisi otomatis oleh resource, tidak tampil di form |
| `icon` | string(64), nullable | wajib jika `section->hasIcon()`; `in:IconOptions::keys()` |
| `title` | string(60) | wajib, maks 60 |
| `description` | string(200) | wajib, maks 200 |
| `is_active` | boolean, default `true` | rule `WithinActiveItemLimit` saat bernilai true |
| `is_emphasized` | boolean, default `false` | hanya bila `supportsEmphasis()`; selain itu selalu false |
| `order` | unsigned int, default 0 | dipakai `reorderable('order')` |
| `created_at`, `updated_at` | timestamps | |

Index: `(section, is_active, order)`.

**Scopes**: `forSection(PageSection)`, `active()`, `ordered()` (by `order`, lalu `id`).

**Invariants**:

- Jumlah `is_active = true` per `section` ≤ `section->maxActiveItems()`. Dijaga oleh rule `WithinActiveItemLimit` di form & ToggleColumn.
- Paling banyak satu `is_emphasized = true` per `section`. Hook `saving`: bila true, set false untuk item lain di section yang sama. Bila section tidak mendukung penonjolan, paksa false.
- Penonjolan hanya berpengaruh visual pada item aktif (render hanya memuat item aktif).

## Tabel `section_headings` → Model `App\Models\SectionHeading`

| Kolom | Tipe | Aturan |
|---|---|---|
| `id` | bigint PK | |
| `section` | string(64), **unique** | cast `PageSection` |
| `title` | string(80) | wajib; pindah baris (`\n`) dipertahankan |
| `subtitle` | string(250), nullable | hanya bila `hasSubtitle()`; selain itu null |
| timestamps | | |

Keberadaan baris = penanda section sudah pernah di-seed (idempotensi seeder).

## Tabel `call_to_actions` → Model `App\Models\CallToAction`

| Kolom | Tipe | Aturan |
|---|---|---|
| `id` | bigint PK | |
| `placement` | string(64), **unique** | cast `CtaPlacement`; tidak bisa diubah |
| `title` | string(80) | wajib; pindah baris dipertahankan |
| `body` | string(300), nullable | subjudul/paragraf |
| `primary_label` | string(40) | wajib |
| `secondary_label` | string(40), nullable | wajib hanya untuk `Home` |
| timestamps | | |

Tidak ada create/delete dari admin. Jumlah baris = jumlah case `CtaPlacement` (9).

## Nilai bawaan — `App\Support\PageContent\DefaultPageContent`

Sumber tunggal untuk seeder, migrasi data, dan fallback CTA (FR-024). Nilai **wajib sama persis** dengan Blade saat ini (`\n` = `<br>` di markup lama).

### Section

| Section | Judul | Subjudul |
|---|---|---|
| WhyChoose | `Mengapa Beralih\nBersama {app_name}?` | `Investasi cerdas untuk masa depan, dirancang dengan presisi tinggi khusus kondisi iklim Indonesia.` |
| HowItWorks | `Sederhana dan Mulus` | `Bagaimana cahaya matahari bertransformasi menjadi energi andal untuk rumah dan bisnis Anda.` |
| CareerValues | `Mengapa Bergabung dengan Kami?` | `Budaya kerja yang mendukung pertumbuhan dan inovasi Anda.` |
| RecruitmentProcess | `Proses Rekrutmen` | – |

### Item (urutan = `order` 0..n)

| Section | # | Ikon | Judul | Deskripsi | Ditonjolkan |
|---|---|---|---|---|---|
| WhyChoose | 1 | `savings` | Efisien & Terjangkau | Turunkan tagihan listrik hingga 80% dengan panel efisiensi tinggi berteknologi monokristalin terbaru. | – |
| WhyChoose | 2 | `verified` | Garansi Panjang | Ketenangan pikiran dengan garansi performa panel hingga 25 tahun dan garansi pengerjaan profesional. | ✅ |
| WhyChoose | 3 | `eco` | Ramah Lingkungan | Kurangi jejak karbon Anda. Satu instalasi setara dengan menanam puluhan pohon setiap tahunnya. | – |
| HowItWorks | 1 | – | Panel & PV Cell | Menyerap sinar matahari dan mengubahnya menjadi energi listrik searah (DC). | – |
| HowItWorks | 2 | – | DC Power | Aliran listrik DC mengalir aman melalui kabel khusus menuju inverter utama. | – |
| HowItWorks | 3 | – | Inverter | Jantung sistem. Mengubah arus DC menjadi arus bolak-balik (AC) untuk alat elektronik. | ✅ |
| HowItWorks | 4 | – | Storage / Grid | Energi digunakan langsung, disimpan di baterai, atau diekspor ke PLN (net-metering). | – |
| CareerValues | 1 | `lightbulb` | Inovasi Berkelanjutan | Kami selalu mencari cara baru untuk memaksimalkan efisiensi energi surya dan meminimalkan dampak lingkungan. | – |
| CareerValues | 2 | `groups` | Kolaborasi Tim | Lingkungan kerja yang inklusif di mana setiap ide didengar dan kolaborasi lintas disiplin didorong. | – |
| CareerValues | 3 | `public` | Dampak Nyata | Pekerjaan Anda secara langsung berkontribusi pada pengurangan emisi karbon dan menciptakan masa depan yang lebih hijau. | – |
| RecruitmentProcess | 1 | – | Lamar | Kirimkan CV dan portofolio Anda melalui portal karir kami. | – |
| RecruitmentProcess | 2 | – | Wawancara HR | Sesi perkenalan untuk menilai kecocokan budaya dan pengalaman dasar. | – |
| RecruitmentProcess | 3 | – | Penilaian Teknis | Wawancara mendalam dengan tim terkait atau studi kasus. | – |
| RecruitmentProcess | 4 | – | Penawaran | Selamat datang di tim! Persiapan onboarding dimulai. | – |

### CTA

| Placement | Judul | Body | Label utama | Label kedua |
|---|---|---|---|---|
| Home | `Siap beralih ke\nenergi matahari?` | Mulai perjalanan hijau Anda hari ini. Tim ahli kami siap membantu menganalisa kebutuhan dan memberikan desain sistem gratis. | Chat via WhatsApp | Isi Form Online |
| ProductCalculator | Bingung pilih yang mana? | Gunakan kalkulator kami untuk memperkirakan kebutuhan daya dan potensi penghematan bulanan Anda. | Coba kalkulator hemat listrik | – |
| ProductClosing | Belum yakin kapasitas yang Anda butuhkan? | Konsultasi gratis dengan tim teknis ahli kami untuk mendapatkan perhitungan yang akurat dan solusi yang tepat. | Konsultasi gratis dengan tim {app_name} | – |
| ProductDetail | Masa Depan Energi Anda | Berinvestasi pada {produk} bukan sekadar mengurangi tagihan listrik, tetapi juga bentuk komitmen terhadap kelestarian bumi — dirancang untuk integrasi mulus dengan arsitektur modern. | Konsultasi kebutuhan Anda | – |
| ArticleIndex | Punya pertanyaan seputar energi surya? | Tim kami siap membantu — dari pemilihan produk hingga estimasi penghematan untuk rumah atau bisnis Anda. | Konsultasi Gratis | – |
| ArticleDetail | Siap beralih ke energi surya? | Konsultasi gratis dengan tim ahli kami | Hubungi via WhatsApp | – |
| About | Ingin tahu lebih lanjut tentang {app_name}? | Ngobrol langsung dengan tim kami | Hubungi via WhatsApp | – |
| Faq | Masih ada pertanyaan lain? | Tim kami siap membantu menjawab kebutuhan spesifik Anda | Hubungi Kami | – |
| Career | Tidak menemukan posisi yang cocok? | Kirimkan CV Anda — kami hubungi saat ada posisi sesuai | Hubungi Kami | – |

> **Nama merek klien ditulis `{app_name}` di nilai bawaan** (Principle I, research R11) dan diganti Nama Situs **sekali** oleh `DefaultPageContent::forCurrentSite()` saat `PageContentInstaller` menanam data (atau saat fallback CTA). Teks yang tersimpan di DB tidak memuat token dan dirender apa adanya. `DefaultPageContent` MUST NOT memuat literal "SUOER".
>
> Implementasi MUST menyalin teks langsung dari Blade saat task dikerjakan (bukan dari tabel ini) dan memverifikasinya dengan `DefaultPageContentTest` + `LegacyMarkupEquivalenceTest`. Tabel ini adalah ringkasan untuk review.

## Lifecycle

- **Item**: dibuat (aktif/nonaktif) → diedit / diurutkan / di-toggle → dihapus (hard delete, dengan konfirmasi). Tidak ada soft delete.
- **Penanaman awal**: `PageContentInstaller::install()`, dipanggil migrasi `…_install_default_page_content` dan oleh `PageContentSeeder`.
- **Heading**: dibuat oleh installer, hanya diedit (tidak dihapus dari admin).
- **CTA**: dibuat oleh installer, hanya diedit.

## Addendum 2026-10-02: Tentang Kami & Info Kontak

### `PageSection` (case tambahan)

| Case | Value | Label | Maks aktif | Ikon | Penonjolan | Judul section | Eyebrow | Kartu besar | Label judul/deskripsi item | Batas item |
|---|---|---|---|---|---|---|---|---|---|---|
| `AboutMission` | `tentang-kami.misi` | Tentang Kami – Misi | 5 | – | – | ya (maks 255) + subjudul | ya (60) | – | Judul / Deskripsi | 120 / 500 |
| `AboutValues` | `tentang-kami.nilai` | Tentang Kami – Nilai | 3 | wajib | – | ya (255) + subjudul | – | ya | Judul / Deskripsi | 120 / 500 |
| `AboutTrust` | `tentang-kami.trust-strip` | Tentang Kami – Trust Strip | 3 | wajib | – | **tidak ada** | – | – | Angka / Keterangan | 60 / 120 |

Section lama tetap: judul maks 80, subjudul 250, item 60 / 200.

### Perubahan kolom

- `section_items.title` string(120), `description` string(500).
- `section_headings.title` string(255), `subtitle` string(500), + `eyebrow` string(60) null, `featured_image_path` string null, `featured_icon` string(64) null, `featured_title` string(160) null, `featured_description` string(500) null.

### Tabel `page_blocks` → Model `App\Models\PageBlock`

| Kolom | Tipe | Aturan |
|---|---|---|
| `id` | bigint PK | |
| `block` | string(64) unique | cast `PageBlockType`, tidak bisa diubah |
| `data` | json | cast array; kunci sesuai tipe blok |
| timestamps | | |

### Enum `App\Enums\PageBlockType`

| Case | Value | Label | Kunci `data` (batas) | Gambar bawaan |
|---|---|---|---|---|
| `AboutHero` | `tentang-kami.hero` | Tentang Kami – Hero | `image_path`, `subtitle` (500) | `images/mockup/produk-1.jpg` |
| `AboutWhoWeAre` | `tentang-kami.siapa-kami` | Tentang Kami – Siapa Kami | `image_path`, `badge_text` (120), `eyebrow` (60), `heading` (255), `body` (rich, 1000), `quote` (rich, 500) | `images/mockup/tentang-kami-2.jpg` |
| `AboutVision` | `tentang-kami.visi` | Tentang Kami – Visi | `eyebrow` (60), `heading` (500), `subtext` (500) | – |
| `ContactInfo` | `kontak.info-kontak` | Kontak – Info Kontak | `whatsapp_label` (40), `operating_hours` (80), `whatsapp_message` (300) | – |

Gambar bawaan kartu besar Nilai: `images/mockup/tentang-kami-3.jpg`. Upload gambar memakai `ImageUploads::storeAsWebp` ke folder `about-page` seperti sebelumnya (lebar maks hero 1920, siapa kami 1000, kartu besar 1200).

### Pemindahan data dari pengaturan lama (`settings`, grup `about_page`)

| Sumber | Tujuan |
|---|---|
| `hero_image_path`, `hero_subtitle` | blok `AboutHero` |
| `siapa_kami_*` (`{app_name}` di body diisi Nama Situs sekali) | blok `AboutWhoWeAre` |
| `visi_eyebrow`, `visi_heading`, `visi_subtext` | blok `AboutVision` |
| `misi_eyebrow`, `misi_heading`, `misi_subtext`, `misi_items[]` | heading + item `AboutMission` (urutan sama, semua aktif) |
| `nilai_heading`, `nilai_subtext`, `nilai_featured_*`, `nilai_items[]` | heading (+ kartu besar) + item `AboutValues` |
| `trust_items[]` (`value` → title, `label` → description) | heading penanda + item `AboutTrust` |

`ContactInfo` bawaan: label "Chat via WhatsApp", jam "Senin - Jumat, 09:00 - 17:00 WIB", pesan "Halo, saya ingin konsultasi tentang solusi tenaga surya {app_name}." (merek diisi Nama Situs sekali).
