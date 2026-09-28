---

description: "Task list for 028-operator-manual (AMC-234)"
---

# Tasks: Manual Operator Panel Admin

**Input**: Design documents from `/specs/028-operator-manual/`

**Prerequisites**: plan.md, spec.md, research.md, contracts/manual-structure.md, quickstart.md

**Tests**: Test dokumen diminta oleh plan (research.md §6; FR-002, FR-004, FR-014, FR-015, FR-016, SC-003, SC-005). Uji label per kolom dan uji baca non-teknis dilakukan manual lewat quickstart.md.

**Organization**: Tasks dikelompokkan per user story. Semua story menulis ke satu file `docs/manual-operator.md`, jadi task dokumen dikerjakan berurutan; tiap story menambah bagiannya sendiri dan bisa diverifikasi dengan `--filter`.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak bergantung task yang belum selesai)
- **[Story]**: User story terkait (US1, US2, US3, US4)

## Aturan penulisan untuk semua task dokumen

Berlaku untuk setiap task yang menulis `docs/manual-operator.md` (sumber: contracts/manual-structure.md):

- Bahasa Indonesia sehari-hari, sapaan "Anda", situs disebut "situs Anda". Tanpa nama klien, nama starter kit, nama vendor panel, URL domain, atau data kontak nyata.
- Tanpa backtick (inline code maupun fenced code block), path file, perintah terminal, atau screenshot.
- Label panel ditulis **tebal** persis seperti di layar. Jalur menu: **Grup** → **Menu**.
- Langkah sebagai daftar bernomor, satu tindakan per langkah. Peringatan sebagai blockquote diawali `> **Perhatian:**`.
- **Label diambil dari kode, bukan dari ingatan**: sebelum menulis subbagian sebuah menu, baca `form()`, `table()`, filter, dan action di file resource/halaman terkait (`->label(...)`, opsi status, `->required()`, batasan upload gambar). Label bawaan Filament berbahasa Indonesia untuk tombol umum ("Buat", "Simpan perubahan", "Hapus", "Ubah") cek di `vendor/filament/*/resources/lang/id/`.
- Heading bagian level 2 (`##`) harus sama persis dengan teks di contracts/manual-structure.md karena diperiksa test. Subbagian memakai level 3 (`###`).
- Setiap bagian baru ditambahkan ke **Daftar isi** dengan tautan anchor.

---

## Phase 1: Setup

**Purpose**: Siapkan file test.

- [X] T001 Buat file test dengan `php artisan make:test --phpunit Docs/OperatorManualTest --no-interaction`, menghasilkan `tests/Feature/Docs/OperatorManualTest.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Test otomatis sebagai "definition of done" dan kerangka dokumen.

**⚠️ CRITICAL**: Selesaikan sebelum menulis isi manual.

- [X] T002 Implementasi `tests/Feature/Docs/OperatorManualTest.php` (PHPUnit class, `RefreshDatabase`, return type `void`, PHPDoc singkat mengacu AMC-234), dengan konstanta `MANUAL = 'docs/manual-operator.md'`:
  - `test_every_panel_menu_is_documented`: buat `Role::create(['name' => 'super_admin'])`, user dari factory dengan `assignRole('super_admin')`, `Gate::before(fn () => true)`, `$this->actingAs($user)`, lalu `Filament::setCurrentPanel(Filament::getPanel('admin'))` dan `Filament::bootCurrentPanel()`. Iterasi `Filament::getNavigation()`: kumpulkan label grup (lewati `null`) dan label setiap item. Kumpulkan label yang tidak muncul di manual sebagai `**{label}**`, lalu `assertSame([], $missing, ...)` dengan pesan yang mendaftar label yang hilang. Tambahkan `assertNotEmpty` pada daftar label supaya test tidak lolos kosong.
  - `test_manual_has_no_code_paths_or_terminal_commands`: manual tidak mengandung backtick sama sekali, dan tidak cocok dengan regex `/\b(php artisan|composer (install|require|update)|npm (run|install)|git (pull|push|clone))\b/i`.
  - `test_manual_is_client_and_vendor_neutral`: manual tidak mengandung (case-insensitive) `Simetri`, `Solarpanel`, `Filament`, `Laravel`.
  - `test_required_section_exists` dengan data provider bernama per story, masing-masing berisi teks heading yang dicek dengan regex `/^## {heading}$/m` (pakai `preg_quote`):
    - `US1 akun` → `Masuk, keluar, dan akun Anda`
    - `US1 peta menu` → `Mengenal panel`
    - `US1 konten` → `Mengelola konten`
    - `US1 jeda tampil` → `Kapan perubahan tampil di situs`
    - `US2 prospek` → `Dasbor dan prospek`
    - `US3 pengaturan` → `Pengaturan situs`
    - `US4 pengguna` → `Pengguna dan peran`
    - `polish masalah umum` → `Masalah umum`
    - `polish istilah` → `Istilah`
  - `test_manual_is_linked_from_readme_and_architecture_doc`: `README.md` mengandung `](docs/manual-operator.md)` dan `docs/arsitektur.md` mengandung `](manual-operator.md)`.
  - Setiap test yang membaca manual lebih dulu `assertFileExists` dengan pesan "docs/manual-operator.md belum dibuat."
  - Jalankan `vendor/bin/pint --dirty --format agent`, lalu `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php` dan pastikan gagal dengan pesan jelas (belum ada dokumen).
- [X] T003 [P] Tambahkan `'manual-operator' => ['docs/manual-operator.md']` ke data provider `documents()` di `tests/Feature/Docs/TechnicalDocsPathsTest.php` (memeriksa tanggal terakhir diperbarui dan tautan relatif; pemeriksaan path otomatis lolos karena manual tanpa backtick)
- [X] T004 [P] Buat kerangka `docs/manual-operator.md`: judul `# Manual Operator Panel Admin`, baris `**Terakhir diperbarui**: 2026-09-26`, satu paragraf pembuka (untuk siapa manual ini, cara memakainya: lompat lewat daftar isi), dan heading `## Daftar isi` yang masih kosong

**Checkpoint**: `php artisan test --compact tests/Feature/Docs` menjalankan semua test dokumen; test struktur dan tautan gagal dengan pesan yang menunjuk bagian yang belum ada.

---

## Phase 3: User Story 1 - Operator baru mengelola konten situs sehari-hari (Priority: P1) 🎯 MVP

**Goal**: Operator bisa login, mengenal semua menu, dan menyelesaikan tugas konten, serta tahu kapan perubahan tampil (FR-003–FR-006, FR-015, FR-017).

**Independent Test**: `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter="every_panel_menu|no_code|neutral|US1"` lulus, lalu cek manual quickstart.md §2 untuk menu konten dan uji baca §3 langkah 3.

- [X] T005 [US1] Tulis bagian `## Masuk, keluar, dan akun Anda` di `docs/manual-operator.md`: buka alamat panel (tambahkan "/admin" di belakang alamat situs Anda), halaman **Masuk ke akun Anda** dengan kolom **Alamat email**, **Kata sandi**, **Ingat saya**, tombol **Masuk**; **Keluar** dari menu pengguna di kanan atas; halaman **Profil** (judul **Profil saya**) untuk mengganti nama dan kata sandi (baca field form halaman profil Breezy di vendor untuk label persis); lupa kata sandi: tidak ada reset mandiri, minta admin mereset lewat **Sistem** → **Pengguna**, atau developer bila Anda admin utama (research.md §3–§4)
- [X] T006 [US1] Tulis bagian `## Mengenal panel` di `docs/manual-operator.md`: penjelasan singkat tata letak (menu kiri, grup yang bisa dilipat, tabel daftar, tombol tambah, form), lalu peta menu sesuai tabel research.md §2 dengan urutan panel: **Dasbor**, lalu tiap grup (**Content**, **Konten Halaman**, **Katalog**, **Prospek & Pesan**, **Blog**, **Portfolio**, **Karir**, **Menu Builder**, **Pengaturan Situs**, **Sistem**) dan setiap menunya ditulis tebal dengan satu kalimat fungsi. Tambahkan catatan bahwa menu yang tampil bisa berbeda per peran, tanpa menjanjikan pembatasan tertentu (research.md §4). Jalankan `test_every_panel_menu_is_documented` sampai lulus
- [X] T007 [US1] Tulis awal bagian `## Mengelola konten` di `docs/manual-operator.md` (pola umum: membuka daftar, mencari/menyaring, tambah, ubah, simpan, hapus dengan `> **Perhatian:**` bahwa hapus bersifat permanen dan sarankan status nonaktif/draft bila ragu; research.md §4), lalu subbagian `### Content → Media Manager` (baca `config/filament-media-manager.php` dan halaman plugin untuk konsep folder dan unggah) dan subbagian untuk menu **Konten Halaman**: **Halaman**, **Banner**, **Testimoni**, **Tim**, **Logo Klien**, **Halaman Tentang Kami**. Sumber label: `app/Filament/Resources/CustomPageResource.php`, `BannerResource.php`, `TestimonialResource.php`, `TeamMemberResource.php`, `ClientLogoResource.php`, `app/Filament/Pages/AboutPageSettingsPage.php`. Tiap subbagian memuat: tampil di bagian situs mana, kolom wajib/penting, status tayang, batasan gambar, langkah bernomor. Untuk **Halaman** (dan menu lain yang punya slug) tambahkan `> **Perhatian:**` soal mengubah slug halaman yang sudah tayang (FR-013)
- [X] T008 [US1] Tambahkan subbagian menu **Katalog** ke `## Mengelola konten` di `docs/manual-operator.md`: **Produk**, **Kategori Produk**, **Peralatan Listrik** (jelaskan bahwa daftar peralatan dipakai kalkulator estimasi di situs). Sumber label: `app/Filament/Resources/ProductResource.php`, `CategoryResource.php`, `ElectricityApplianceResource.php`
- [X] T009 [US1] Tambahkan subbagian menu **Blog**, **Portfolio**, dan **Karir** ke `## Mengelola konten` di `docs/manual-operator.md`: **Artikel**, **Kategori Artikel**, **Portfolio**, **Kategori Portfolio**, **Lowongan Kerja**. Sumber label: `app/Filament/Resources/ArticleResource.php`, `ArticleCategoryResource.php`, `PortfolioProjectResource.php`, `PortfolioCategoryResource.php`, `JobOpeningResource.php`. Untuk **Lowongan Kerja**, sebutkan bahwa halaman karir bisa dinonaktifkan di **Pengaturan Umum** (cek `app/Settings/SiteSettings.php` dan `app/Filament/Pages/SiteSettingsPage.php` untuk label toggle)
- [X] T010 [US1] Tambahkan subbagian **Menu Builder** ke `## Mengelola konten` di `docs/manual-operator.md`: hubungan **Lokasi Menu** (misal header, footer) dan **Item Menu**, cara menambah item, mengatur urutan dan sub-menu. Sumber label: `app/Filament/Resources/MenuLocationResource.php`, `MenuItemResource.php`
- [X] T011 [US1] Tulis bagian `## Kapan perubahan tampil di situs` di `docs/manual-operator.md`: sebagian halaman butuh hingga 5 menit; banner Home, Tim, Testimoni, Logo Klien, dan Halaman Tentang Kami biasanya langsung; produk, artikel, dan portfolio bisa terlambat hingga 5 menit; cara memeriksa (muat ulang, jendela penyamaran/incognito) dan kapan menghubungi developer (research.md §4; cek ulang `docs/arsitektur.md` bagian "Cache halaman publik")
- [X] T012 [US1] Isi `## Daftar isi` di `docs/manual-operator.md` untuk bagian US1 (termasuk subbagian menu konten), lalu jalankan `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter="every_panel_menu|no_code|neutral|US1"` dan `php artisan test --compact tests/Feature/Docs/TechnicalDocsPathsTest.php --filter=manual-operator` sampai lulus

**Checkpoint**: Manual sudah berguna untuk operator konten (MVP) dan bisa di-review.

---

## Phase 4: User Story 2 - Operator menindaklanjuti prospek (Priority: P2)

**Goal**: Operator memahami Dasbor dan bisa menindaklanjuti Pesan Masuk dan Lead Kalkulator (FR-007, FR-008).

**Independent Test**: `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter=US2` lulus, lalu cek quickstart.md §2 untuk **Dasbor**, **Pesan Masuk**, **Lead Kalkulator**.

- [X] T013 [US2] Tulis awal bagian `## Dasbor dan prospek` di `docs/manual-operator.md`, ditempatkan setelah `## Mengenal panel` dan sebelum `## Mengelola konten` (urutan contracts/manual-structure.md). Subbagian `### Dasbor`: empat kartu (**Pesan masuk baru**, **Lead kalkulator baru**, **Prospek 30 hari**, **Konversi lead kalkulator**, termasuk "Belum ada data"), klik kartu membuka daftar terfilter, tabel **Perlu ditindaklanjuti** (kolom, badge **Terlambat** untuk prospek lebih dari 48 jam, klik baris, "Semua prospek sudah ditindaklanjuti"), grafik **Prospek per minggu** (12 minggu, dua warna). Sumber: `app/Filament/Widgets/LeadStatsOverview.php`, `LeadsNeedingFollowUp.php`, `WeeklyLeadsChart.php`, `resources/views/filament/widgets/leads-needing-follow-up.blade.php`, `specs/027-leads-dashboard/contracts/dashboard-ui.md`. Sebutkan bahwa hitungan hari dan minggu mengikuti zona waktu di **Pengaturan Umum**
- [X] T014 [US2] Tambahkan subbagian `### Pesan Masuk` dan `### Lead Kalkulator` di `## Dasbor dan prospek` di `docs/manual-operator.md`: asal masing-masing (form kontak vs. form hitung estimasi di situs), filter status dan filter "belum dihubungi"/"belum di-follow-up", arti tiap status (Pesan Masuk: **Baru**, **Sudah Dihubungi**, **Selesai**; Lead Kalkulator: **Baru**, **Sudah Dihubungi**, **Qualified**, **Deal**, **Batal**), aksi **Tandai Sudah Dihubungi**, membuka detail, mengubah status, dan untuk lead kalkulator mengisi catatan follow-up, PIC, dan waktu follow-up. Sumber: `app/Filament/Resources/ContactSubmissionResource.php`, `app/Filament/Resources/CalculatorLeadResource.php`
- [X] T015 [US2] Tambahkan bagian US2 ke `## Daftar isi` di `docs/manual-operator.md` dan jalankan `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter="US2|no_code|neutral"` sampai lulus

**Checkpoint**: US1 dan US2 lengkap dan teruji.

---

## Phase 5: User Story 3 - Admin klien mengatur identitas dan pengaturan situs (Priority: P3)

**Goal**: Admin memahami setiap halaman **Pengaturan Situs** dan mode pemeliharaan (FR-009, FR-010, FR-013).

**Independent Test**: `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter=US3` lulus, lalu cek quickstart.md §2 untuk halaman pengaturan dan pita mode pemeliharaan.

- [X] T016 [US3] Tulis awal bagian `## Pengaturan situs` di `docs/manual-operator.md` (setelah `## Mengelola konten`), subbagian `### Pengaturan Umum`: identitas situs, kontak perusahaan, bahasa, zona waktu, tautan legal, toggle modul karir, halaman error, dan mode pemeliharaan. Untuk mode pemeliharaan: cara menyalakan/mematikan, pengunjung melihat halaman pemeliharaan sementara pengguna yang login tetap melihat situs normal, periksa lewat jendela penyamaran, pita merah **Mode Pemeliharaan aktif — pengunjung publik melihat halaman pemeliharaan.** di panel, dengan `> **Perhatian:**` agar tidak lupa mematikannya. Sumber: `app/Filament/Pages/SiteSettingsPage.php`, `app/Settings/SiteSettings.php`, `app/Providers/Filament/AdminPanelProvider.php`, `tests/Feature/Public/MaintenanceModeTest.php`
- [X] T017 [US3] Tambahkan subbagian `### Tampilan`, `### SEO`, `### Media Sosial` di `## Pengaturan situs` di `docs/manual-operator.md`: apa yang diatur dan bagian situs yang terpengaruh (logo, favicon, warna, font dari daftar pilihan; judul dan deskripsi untuk mesin pencari; tautan media sosial di header/footer dan tombol bagikan). Sumber: `app/Filament/Pages/AppearanceSettingsPage.php`, `SeoSettingsPage.php`, `SocialSettingsPage.php`
- [X] T018 [US3] Tambahkan subbagian `### Scripts & Analytics` dan `### Kalkulator Estimasi` di `## Pengaturan situs` di `docs/manual-operator.md`, masing-masing dengan `> **Perhatian:**`: kode dari pihak ketiga bisa merusak tampilan atau memperlambat situs, koordinasikan dengan developer; asumsi tarif/cakupan/eskalasi/faktor investasi mengubah angka estimasi untuk lead berikutnya (lead lama tetap memakai asumsi saat itu). Sumber: `app/Filament/Pages/ScriptSettingsPage.php`, `app/Filament/Pages/CalculatorSettingsPage.php`, `app/Settings/CalculatorSettings.php`
- [X] T019 [US3] Tambahkan bagian US3 ke `## Daftar isi` di `docs/manual-operator.md` dan jalankan `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter="US3|no_code|neutral"` sampai lulus

**Checkpoint**: US1–US3 lengkap dan teruji.

---

## Phase 6: User Story 4 - Admin klien mengelola pengguna dan akun sendiri (Priority: P4)

**Goal**: Admin bisa mengelola akun staf, peran, dan membaca log aktivitas (FR-011).

**Independent Test**: `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter=US4` lulus, lalu cek quickstart.md §2 untuk menu **Sistem**.

- [X] T020 [US4] Tulis bagian `## Pengguna dan peran` di `docs/manual-operator.md` (setelah `## Pengaturan situs`), ditandai "khusus admin utama": `### Pengguna` (buat akun dengan **Nama**, **Email**, **Kata Sandi**, **Peran**; mereset kata sandi staf lewat form edit; mencabut akses dengan menghapus akun atau mengosongkan peran, cek perilaku `User::canAccessPanel()` di `app/Models/User.php`), `### Peran` (peran menentukan menu yang terlihat; ubah izin hanya bila paham, atau minta developer), `### Log Aktivitas` (melihat siapa mengubah apa, hanya admin utama). Sumber: `app/Filament/Resources/UserResource.php`, `app/Filament/Resources/RoleResource.php`, `app/Providers/Filament/AdminPanelProvider.php`
- [X] T021 [US4] Tambahkan bagian US4 ke `## Daftar isi` di `docs/manual-operator.md` dan jalankan `php artisan test --compact tests/Feature/Docs/OperatorManualTest.php --filter="US4|no_code|neutral"` sampai lulus

**Checkpoint**: Keempat story lengkap dan teruji.

---

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T022 Tulis bagian `## Masalah umum` di `docs/manual-operator.md` (setelah `## Kapan perubahan tampil di situs`), minimal: lupa kata sandi, menu tidak terlihat, perubahan belum tampil di situs, data terhapus tidak sengaja, gambar gagal diunggah, dan "Kapan menghubungi developer" (FR-012). Tautkan ke bagian terkait dengan anchor, bukan mengulang isinya
- [X] T023 Tulis bagian `## Istilah` di `docs/manual-operator.md` (bagian terakhir): slug, SEO, meta description, cache, draft/terbit, dan istilah lain yang muncul di manual tanpa penjelasan (FR-002)
- [X] T024 Lengkapi `## Daftar isi` di `docs/manual-operator.md` (semua bagian dan subbagian, urutan sesuai contracts/manual-structure.md) dan periksa semua peringatan wajib FR-013 ada: hapus data, slug, Scripts & Analytics, Kalkulator Estimasi, mode pemeliharaan
- [X] T025 [P] Tambahkan tautan manual ke `README.md`: di bawah bagian dokumentasi, `- [Manual operator panel admin](docs/manual-operator.md) — untuk operator/admin klien: login, mengelola konten, prospek, dan pengaturan situs`
- [X] T026 [P] Tambahkan `- [Manual operator panel admin](manual-operator.md) (untuk operator klien, bukan developer)` ke bagian "Dokumen terkait" di `docs/arsitektur.md`; biarkan baris `**Terakhir diperbarui**` dengan format `YYYY-MM-DD` saja (diperiksa `TechnicalDocsPathsTest`)
- [X] T027 Jalankan `vendor/bin/pint --dirty --format agent`, lalu `php artisan test --compact tests/Feature/Docs` sampai semua lulus
- [X] T028 Verifikasi manual sesuai `specs/028-operator-manual/quickstart.md` §2 (cocokkan label kolom, tombol, dan status di setiap subbagian dengan panel lokal) dan §4 (netral klien); perbaiki perbedaan di `docs/manual-operator.md`. Uji baca non-teknis §3 (SC-001, SC-002) dicatat sebagai tindak lanjut untuk tim, lalu tanyakan ke user apakah ingin menjalankan seluruh test suite

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: T001 tanpa ketergantungan.
- **Foundational (Phase 2)**: T002 butuh T001. T003 dan T004 independen dari T002 (file berbeda).
- **User Stories (Phase 3–6)**: butuh Phase 2. Semua menulis ke `docs/manual-operator.md`, jadi kerjakan berurutan P1 → P2 → P3 → P4. Secara isi tidak ada story yang bergantung pada story lain; US2 hanya menyisipkan bagiannya sebelum `## Mengelola konten`.
- **Polish (Phase 7)**: T022–T024 setelah story yang ingin dirilis selesai. T025 dan T026 bisa dikerjakan kapan saja setelah Phase 2. T027–T028 terakhir.

### Within Each User Story

- Baca file sumber label → tulis subbagian → tambah ke Daftar isi → jalankan test story.

### Parallel Opportunities

- T003 ∥ T004 (file berbeda).
- T025 ∥ T026 (file berbeda), bisa juga paralel dengan task dokumen mana pun.
- Task di dalam story tidak paralel karena menulis ke file yang sama.

## Parallel Example: Phase 2

```text
T003 Tambah manual-operator ke data provider tests/Feature/Docs/TechnicalDocsPathsTest.php
T004 Buat kerangka docs/manual-operator.md
```

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1–2: test dan kerangka dokumen.
2. Phase 3: login, peta menu, semua menu konten, jeda tampil.
3. **Stop & validasi**: test US1 dan cakupan menu lulus; cek label menu konten (quickstart §2).

### Incremental Delivery

1. MVP (US1) → review.
2. Dasbor dan prospek (US2) → test + cek label.
3. Pengaturan situs (US3) → test + cek label.
4. Pengguna dan peran (US4) → test + cek label.
5. Polish: masalah umum, istilah, tautan README/arsitektur, verifikasi akhir.
