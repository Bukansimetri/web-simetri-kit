# Research: Halaman Legal yang Dapat Diedit

Tidak ada `NEEDS CLARIFICATION` (label tanggal sudah diputuskan dihilangkan).

## R1. Penyimpanan data legal

- **Decision**: Kolom `legal` JSON di `custom_pages` (cast `array`) dan kolom `template` string default `standar`. `content` dijadikan nullable.
- **Rationale**: Bagian dan kartu selalu diedit sebagai satu dokumen; Repeater Filament bekerja langsung dengan array JSON (termasuk urutan). Tidak perlu tabel bagian/kartu. Data legal tetap ada saat template diganti (FR-009) karena kolomnya terpisah dari `content`.
- **Alternatives**: Tabel `legal_sections` + `legal_cards` (relasi, migrasi, dan Repeater relationship lebih kompleks tanpa manfaat query); menyimpan HTML utuh di `content` (tidak bisa menghasilkan daftar isi, kartu, dan sorotan secara terstruktur).

## R2. Admin

- **Decision**: Di `CustomPageResource::form()`:
  - `Select::make('template')` (Standar / Dokumen Legal), `live()`, default Standar.
  - `RichEditor content`: `visible` dan `required` hanya untuk Standar.
  - `Section 'Dokumen Legal'` (visible untuk Legal), dengan `statePath('legal')`, berisi:
    - `FileUpload hero_image_path` (gambar, WebP, maks 10 MB, folder `legal-pages`)
    - `Textarea subtitle`
    - `RichEditor intro`
    - `Fieldset Sorotan`: `TextInput highlight_title`, `Textarea highlight_body`
    - `Repeater sections` (reorderable, collapsible, `itemLabel` = judul), berisi `TextInput label`, `TextInput title` (wajib), `RichEditor body`, `Repeater cards` (`Select icon` dari `MaterialSymbolsIcons::selectOptions()` dengan `Rule::in`, `TextInput title`, `Textarea text`), dan `Textarea note`
    - `Fieldset Kontak`: judul, teks, label WhatsApp, pesan WhatsApp, email
    - `FileUpload pdf_path` (`application/pdf`, maks 10240 KB, folder `legal-pages/pdf`, nama asli dipertahankan)
    - `Fieldset CTA`: judul, teks, label tombol, URL
  - Tabel: kolom `template` (badge).
  - Saat menyimpan halaman legal, `content` diisi null.
- **Rationale**: Pola form kondisional (`visible(fn (Get $get) => …)`) sudah dipakai FAQ dan Artikel.

## R3. Tampilan publik

- **Decision**: `CustomPageController` mengembalikan `pages.custom-page.legal` bila `template === Legal`, selain itu view lama (FR-002).

View legal:
- **Hero**: `x-sections.page-hero` (breadcrumb = judul, subjudul, gambar `PageContent::imageUrl(hero_image_path, 'images/mockup/tentang-kami-3.jpg')`).
- **Layout**: `grid lg:grid-cols-[300px_minmax(0,1fr)]`, sidebar `lg:sticky lg:top-28` di kiri sesuai desain; di ponsel sidebar di atas.
- **Sidebar**:
  - Kartu "Daftar Isi" dengan judul tetap "Daftar Isi" (tidak ada di desain sebagai data, cukup teks statis).
  - Tombol PDF bila `pdf_path` ada dan berkas ada di disk.
  - Kotak kontak bila judul atau teks diisi. Tombol WhatsApp memakai `SiteSettings::whatsappUrl(pesan)` dan disembunyikan bila null; email `mailto:`.
- **Isi**:
  - Kartu putih berisi pembuka (rich text dibersihkan) dan kotak sorotan bila judul atau isi ada.
  - Setiap bagian `<section id="bagian-{n}">` berisi nomor bulat, label kecil "PASAL 0n · {LABEL}" bila label diisi, judul, isi (dibersihkan), grid kartu (`grid sm:grid-cols-2` atau `lg:grid-cols-3` bila 3 kartu atau kelipatannya), dan catatan kecil.
- **Daftar isi aktif**: Alpine `x-data` dengan `IntersectionObserver` (rootMargin `-30% 0px -60% 0px`); tautan aktif diberi kelas aktif. Tanpa JS, tautan anchor tetap berfungsi.
- **CTA**: banner biru seperti `cta-band` tetapi dengan teks dari data legal (tampil bila judul diisi); tombol ke `cta.button_url` (default `/kontak`).
- **Rationale**: Memakai komponen dan token yang sudah ada sehingga konsisten dengan halaman lain.

## R4. Sanitasi

- **Decision**: `intro`, `sections.*.body` dirender lewat `PageContent::richText()` (memakai `HtmlSanitizer::clean`). Teks biasa (`subtitle`, kartu, catatan, kontak, CTA) di-escape dengan `{{ }}`.
- **Catatan**: Template Standar tetap merender `content` mentah seperti sekarang (FR-002, di luar cakupan).

## R5. Isi awal halaman legal

- **Decision**:
  - `DefaultLegalPages::pages()` mengembalikan dua entri (`kebijakan-privasi`, `syarat-ketentuan`), masing-masing berisi title, meta_description, dan struktur `legal` lengkap.
  - Teks disalin persis dari `code.html` kedua desain: paragraf, poin berlabel tebal (sebagai `<ul><li><strong>Label:</strong> isi</li></ul>`), kartu (judul + teks + ikon mendekati ikon desain dari daftar kurasi), dan catatan bergaya miring.
  - Placeholder: `{app_name}` dari Nama Situs. `{company_email}`, `{company_phone}`, `{company_address}` dari Pengaturan Umum; bila kosong, memakai nilai dari desain (`hello@suoer.id`, `(021) 5890-7722`, `Gedung Energi Hijau Lt. 8, Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190`) sebagai cadangan (FR-014).
  - `LegalPageInstaller::install()`: untuk tiap entri, `CustomPage::firstWhere('slug')`; bila tidak ada, buat dengan template Legal (FR-013, FR-015). Lewati bila kolom `template` belum ada.
- **Gambar hero bawaan**: tidak disalin ke storage. View jatuh ke gambar mockup yang ada bila `hero_image_path` kosong.

## R6. Isi awal FAQ

- **Decision**:
  - Lima pertanyaan `FaqItemSeeder` (dengan "SUOER" diganti `{app_name}`) dan kategorinya dipindah ke `DefaultPageContent::faqs()[FaqPlacement::Faq->value]`, dengan elemen berisi `category`.
  - `PageContentInstaller::installFaqs()` sudah memasang per tempat hanya bila tempat itu kosong; kategori ikut disimpan.
  - `FaqItemSeeder::run()` diganti menjadi `PageContentInstaller::install()` agar tidak ada dua sumber.
- **Dampak**: `FaqDefaultsInstallerTest::test_demo_seeder_is_not_required_for_the_defaults` (yang menegaskan 0 entri `faq` setelah migrasi) diperbarui menjadi 5 entri (keputusan baru). `FaqPlacementRenderTest` sudah menghapus entri sendiri di setiap tes.

## R7. Migrasi data dan urutan

- **Decision**: Migrasi skema (`template`, `legal`, `content` nullable) diikuti migrasi data `install_legal_pages_and_faq_defaults` yang memanggil `PageContentInstaller::install()` (FAQ) dan `LegalPageInstaller::install()`.
- **Catatan**: `SiteSettings` dibaca saat migrasi data; properti `footer_description` sudah dimigrasi lebih awal (2026_09_23), jadi tidak ada `MissingSettings`.

## R8. Pengujian

- **Admin**:
  - Pilih template Legal, isi bagian, kartu, dan PDF, lalu simpan; data tersimpan di `legal`.
  - Kolom Standar tersembunyi dan sebaliknya.
  - Validasi PDF (tipe dan ukuran) serta ikon kartu (daftar kurasi).
  - Ganti template tidak menghapus data legal.
- **Publik**:
  - Halaman Standar identik (snapshot markup tes lama).
  - Halaman legal menampilkan hero tanpa label tanggal, bagian bernomor, label "PASAL", kartu, daftar isi beranchor, kontak (WA tersembunyi bila nomor kosong), PDF hanya bila berkas ada, CTA opsional, dan sanitasi skrip.
- **Installer**:
  - Migrasi membuat 2 halaman dan 5 FAQ.
  - Placeholder terisi dari pengaturan dengan cadangan.
  - Idempoten.
  - Halaman dengan slug sama yang sudah ada tidak diubah.
  - Seeder manual tanpa duplikasi.
- **Footer**: tautan Kebijakan Privasi dan Syarat & Ketentuan mengembalikan 200.
