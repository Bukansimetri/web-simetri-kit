# Research: Kelola Section Konten & CTA dari Panel Admin

**Feature**: `029-why-choose-admin` | **Date**: 2026-09-30

Setiap keputusan mengikuti format: Decision / Rationale / Alternatives considered.

## R1. Bentuk penyimpanan item section

- **Decision**: Satu tabel `section_items` dengan kolom `section` (string, di-cast ke enum `App\Enums\PageSection`), dikelola oleh **empat Filament Resource terpisah** (satu per section) yang masing-masing men-scope query ke section-nya. Aturan per section (batas aktif, ikon, penonjolan, subjudul, format nomor) didefinisikan di enum.
- **Rationale**: Pengguna memilih pendekatan "Model + Resource per section" (opsi D). Empat resource memberi admin satu menu per section seperti Testimoni. Keempat section punya bentuk data yang sama (judul, deskripsi, aktif, urutan, ikon/penonjolan opsional), jadi empat tabel dan empat model identik hanya menambah duplikasi (Principle V). Aturan yang berbeda per section cukup berupa method di enum.
- **Alternatives considered**:
  - *Empat model + empat tabel*: paling "murni", tetapi migrasi, factory, cache hook, validasi, dan test ditulis 4×. Ditolak (duplikasi tanpa manfaat).
  - *Satu resource dengan filter section*: menu admin lebih ringkas, tetapi admin harus memilih filter dan batas aktif per section jadi membingungkan. Ditolak karena bertentangan dengan pilihan "Resource per section".
  - *Kolom JSON di Spatie Settings* (pola `AboutPageSettings::nilai_items`): tidak mendukung urutan seret, aktif/nonaktif per baris, atau validasi batas aktif dengan baik. Ditolak.

## R2. Judul & subjudul section

- **Decision**: Tabel `section_headings` (satu baris per section, `section` unik) dengan model `SectionHeading`. Diedit lewat **header action "Ubah Judul Section"** (modal form) di halaman daftar setiap resource section.
- **Rationale**: Judul berada di lokasi yang mudah ditemukan dari modul section-nya (FR-009) tanpa menambah halaman Settings baru. Baris heading juga menjadi penanda "section ini sudah pernah di-seed" untuk idempotensi seeder (R4).
- **Alternatives considered**: *Spatie Settings baru (`PageSectionSettings`)*: butuh settings migration per field dan halaman admin terpisah dari daftar item. Ditolak karena lebih jauh dari konteks admin. *Kolom di `section_items`*: salah tempat karena heading bukan item. Ditolak.

## R3. CTA

- **Decision**: Tabel `call_to_actions` (`placement` unik, di-cast ke enum `App\Enums\CtaPlacement`) dengan model `CallToAction` dan satu `CallToActionResource` berisi halaman **List + Edit saja** (`canCreate()` / `canDelete()` = false). Tujuan tombol, ikon, dan markup tetap di Blade. Hanya teks yang dibaca dari database.
- **Rationale**: Penempatan tetap sembilan dan terikat desain halaman (FR-019). Menyimpan tujuan tombol di Blade menjamin FR-022 (tidak bisa diubah admin) tanpa logika tambahan.
- **Alternatives considered**: *Settings per halaman*: sembilan grup settings × empat field, dan tersebar di banyak halaman admin. Ditolak. *Kolom `url` yang dikunci*: menambah data tanpa kegunaan. Ditolak.

## R4. Data awal di production (konflik dengan kebiasaan "seeder hanya demo")

- **Temuan**: `docs/deployment.md` hanya menjalankan `php artisan migrate --force`, tanpa `db:seed`. Constitution (Deployment Standards) melarang **konten demo** ikut ter-seed otomatis di production. Konten fitur ini bukan demo. Ini konten live klien yang sekarang ditulis di Blade. Jika hanya ada di seeder, production akan kehilangan keempat section (tersembunyi karena kosong, FR-013) dan CTA jatuh ke fallback. Hasilnya melanggar FR-015 dan SC-001.
- **Decision**:
  1. `App\Support\PageContent\DefaultPageContent` menjadi **satu-satunya sumber** nilai bawaan (heading, item, CTA), berisi teks persis dari Blade saat ini.
  2. `App\Support\PageContent\PageContentInstaller::install()` berisi logika tanam konten yang idempoten.
  3. Migrasi data `…_install_default_page_content.php` memanggil **installer (bukan seeder)**, sehingga production mendapat konten lewat `migrate --force` dan aturan constitution "seeder tidak auto-run di migrasi production" tetap dipatuhi.
  4. `Database\Seeders\PageContentSeeder` hanya memanggil installer. Seeder ini didaftarkan di `DatabaseSeeder` untuk `db:seed` lokal/instalasi baru.
- **Idempotensi**: Section di-seed **hanya jika** belum punya baris `section_headings`. Kalau admin sudah pernah menyentuh section (termasuk menghapus semua item), seeder tidak menambah apa pun. CTA memakai `firstOrCreate(['placement' => …])`, jadi tidak pernah menimpa teks admin.
- **Rationale**: Mengikuti preseden settings migration `about_page` yang juga menanam konten lewat migrasi. Memisahkan logika ke installer menghindari pelanggaran constitution (temuan analisis C1). Seeder tetap ada sesuai permintaan pengguna.
- **Alternatif yang ditolak (setelah analisis)**: *Migrasi memanggil `PageContentSeeder` langsung*: melanggar aturan harfiah constitution "seeders … never auto-run in production migrations".
- **Alternatives considered**: *Fallback di kode tanpa data di DB*: admin tidak akan melihat item apa pun untuk diedit sampai membuat ulang semuanya. Ditolak. *Menambahkan `db:seed --class` ke dokumen deploy*: rawan terlupa di deploy klien lama. Ditolak.

## R5. Validasi batas item aktif & satu item ditonjolkan

- **Decision**:
  - Custom rule `App\Rules\WithinActiveItemLimit` (menerima section + id record yang sedang diedit) dipakai di **form** (`Toggle::make('is_active')->rules([...])`) dan di **tabel** (`ToggleColumn::make('is_active')->rules([...])`). Filament v3 `ToggleColumn` memakai trait `CanBeValidated`, sudah dicek di `vendor/filament/tables/src/Columns/ToggleColumn.php`.
  - Penonjolan tunggal: hook `saving` di `SectionItem`. Jika `is_emphasized` = true, item lain di section yang sama di-set false dalam transaksi yang sama.
- **Rationale**: Satu rule dipakai di dua jalur, dengan pesan konsisten yang menyebut batasnya. Pelepasan tanda otomatis sesuai FR-008.
- **Alternatives considered**: *Guard di model yang melempar exception*: pesan error di ToggleColumn tidak tampil rapi. Ditolak sebagai mekanisme utama.

## R6. Cache

- **Decision**: Konten section & CTA **dibaca langsung** (query kecil) di komponen Blade melalui `App\Support\PageContent\PageContent`, **tidak** lewat `rememberPublicPage`.
- **Rationale**: CTA tampil di halaman dengan cache per slug (`public-page:produk.show:{slug}`, `public-page:artikel.show:{slug}`), sehingga invalidasi per slug tidak praktis. Pola "data dibaca langsung di Blade tidak ikut cache" sudah terdokumentasi di `docs/panduan-section.md` §6. Setiap halaman hanya menambah ≤ 3 query kecil berindeks. FR-014 (perubahan langsung terlihat) terpenuhi tanpa hook invalidasi.
- **Alternatives considered**: *Cache per key + `Cache::forget` di hook model*: menambah jalur bug invalidasi untuk penghematan yang tidak terukur. Ditolak (Principle V). Bisa ditambahkan nanti bila profil performa membutuhkannya.

## R7. Menjamin tampilan identik (FR-012a, SC-001, SC-006)

- **Decision**:
  1. **Sebelum** mengubah Blade, render setiap section & CTA dari kode lama dan simpan HTML-nya sebagai fixture: `tests/Fixtures/legacy-page-content/*.html`.
  2. Test `LegacyMarkupEquivalenceTest` merender halaman baru (dengan data awal dari migrasi), mengekstrak fragmen yang sama lewat XPath dengan helper `Tests\Support\LegacyMarkup::extract()` yang juga dipakai saat membuat fixture, lalu membandingkannya **persis** (`assertSame`) dengan fixture setelah normalisasi spasi. Helper yang sama di kedua sisi membuat efek serialisasi DOM identik. Ini lebih ketat daripada "fixture terkandung di halaman".
  3. Test `DefaultPageContentTest` membandingkan `DefaultPageContent` dengan literal teks lama (SC-006).
  4. Cek visual manual tangkapan layar sebelum/sesudah (mobile & desktop) di quickstart.
- **Aturan render yang menjaga markup**: pindah baris judul dirender sebagai `<br>` persis seperti markup lama (`e()` lalu ganti `\n` → `<br>`, bukan `nl2br` yang menghasilkan `<br />\n`). Dekorasi berbasis posisi (`-rotate-1`, `md:mt-12`, nomor `01`/`1`) tetap di Blade sebagai array berindeks posisi.
- **Keterbatasan ekstraksi DOM**: HTML dimuat dengan prefiks `<?xml encoding="UTF-8">` agar karakter seperti `—` tidak rusak. Parser libxml membuang atribut dengan nama tidak valid (Alpine `@click`, `:class`), sehingga perubahan pada atribut itu tidak terdeteksi test. Ke-13 fragmen saat ini tidak memuat atribut Alpine. Bila fragmen baru memuatnya, verifikasi manual lewat tangkapan layar.
- **Alternatives considered**: *`assertStringContainsString` pada halaman penuh*: kurang ketat dan rentan salah cocok. Ditolak. *Snapshot test halaman penuh*: halaman memuat token CSRF & data dinamis lain sehingga rapuh. Ditolak. *Hanya uji manual*: tidak mencegah regresi di masa depan. Ditolak.

## R8. Pemilih ikon

- **Decision**: `Select` dengan opsi dari `App\Support\PageContent\IconOptions` (daftar kurasi Material Symbols, minimal berisi `savings`, `verified`, `eco`, `lightbulb`, `groups`, `public` + ±20 ikon relevan), `->allowHtml()` untuk pratinjau `<span class="material-symbols-outlined">`, dan `->searchable()`. Validasi `Rule::in(IconOptions::keys())`.
- **Rationale**: Panel admin sudah memuat font Material Symbols (`app/Providers/Filament/AdminPanelProvider.php:49`), jadi pratinjau bisa langsung dipakai. Daftar kurasi mencegah nama ikon mentah tampil di halaman (Edge Case).

## R9. Penanda nama produk di CTA "Detail Produk"

- **Decision**: Token `{produk}` di paragraf diganti dengan `Str::lower($product->name)` saat render (perilaku sekarang). Paragraf di-escape lebih dulu, lalu token diganti dengan nama produk yang juga di-escape. Helper text di form menjelaskan token ini.
- **Rationale**: Mengikuti pola `{app_name}` di `AboutPageSettings::sanitizedSiapaKamiBody()`.

## R10. Hak akses, navigasi, dan log aktivitas

- **Decision**: Tanpa policy baru, sama seperti Testimoni (FR-017). Semua resource di grup navigasi `Konten Halaman` dengan label: "Beranda – Mengapa Beralih", "Beranda – Cara Kerja", "Karir – Mengapa Bergabung", "Karir – Proses Rekrutmen", "CTA". Tidak menambah `LogsActivity` karena modul konten lain (Testimoni, Banner) juga tidak memakainya.

## R11. Nama merek klien di konten bawaan (Principle I)

> **Revisi 2026-10-02 (permintaan pengguna)**: teks TIDAK lagi diganti saat render. Token `{app_name}` hanya dipakai di nilai bawaan dan diisi Nama Situs sekali saat instalasi (`DefaultPageContent::forCurrentSite()`); setelah itu teks sepenuhnya mengikuti isian admin. `PageContent::text()` dihapus.

- **Temuan (analisis C2)**: Teks lama memuat "SUOER" di 3 tempat: judul "Mengapa Beralih", label tombol "Produk – CTA Penutup", dan judul "Tentang Kami – CTA". Menanamnya di `DefaultPageContent` dan migrasi berarti setiap instalasi klien baru menampilkan merek SUOER. Ini melanggar Principle I ("client-specific data MUST NOT be hardcoded in views, migrations, or business logic").
- **Decision**: Nilai bawaan menulis merek sebagai token `{app_name}`. Semua teks section & CTA dirender lewat `PageContent::text()`, yang mengganti token dengan `SiteSettings::site_name ?: config('app.name')`. Resolusi ini sama dengan `$appName` di `resources/views/pages/tentang-kami.blade.php`, dan preseden token `{app_name}` sudah ada di `AboutPageSettings::sanitizedSiapaKamiBody()`. Admin juga bisa memakai token ini.
- **Konsekuensi**:
  - Test kesetaraan markup harus men-set `site_name = 'SUOER'` agar output identik dengan fixture lama.
  - Production akan identik **hanya jika** Nama Situs = "SUOER" (atau `APP_NAME=SUOER` saat Nama Situs kosong). Langkah verifikasi ini masuk quickstart §5 & `docs/deployment.md`.
- **Alternatives considered**: *Tetap literal "SUOER"*: melanggar Principle I. *Mengosongkan nilai bawaan*: section tersembunyi dan tampilan berubah, melanggar FR-015.
