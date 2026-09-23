---
description: "Task list for Site Settings"
---

# Tasks: Site Settings

**Input**: Design documents from `/specs/023-site-settings/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/)

**Tests**: **WAJIB, bukan opsional.** FR-060 menjadikan feature test syarat selesai untuk setiap titik penerapan, dan Prinsip IV konstitusi mewajibkan setiap modul punya feature test sebelum dianggap selesai. Pengaturan yang tersimpan tanpa test pembuktian dianggap belum selesai (FR-059).

**Organization**: Task dikelompokkan per user story agar tiap story dapat diimplementasi, diuji, dan dirilis mandiri.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat dikerjakan paralel (berkas berbeda, tanpa dependensi pada task yang belum selesai)
- **[Story]**: User story yang dilayani task tersebut (US1 sampai US9)

## Path Conventions

Monolit Laravel di root repositori: `app/`, `database/`, `resources/`, `tests/`. Tidak ada folder dasar baru. Tidak ada dependensi baru (plan.md Technical Context).

---

## Phase 1: Setup

**Purpose**: Menetapkan garis dasar agar Phase 2 dapat dibuktikan tidak mengubah perilaku apa pun.

- [ ] T001 Verifikasi berada di branch `023-site-settings` dan `php artisan test --compact` hijau seluruhnya sebagai garis dasar sebelum perubahan apa pun
- [ ] T002 Simpan cuplikan keluaran halaman publik sebagai pembanding regresi Phase 2: jalankan server lokal lalu simpan hasil `curl` halaman `/`, `/produk`, `/artikel`, `/kontak` ke berkas sementara di luar repositori (lihat quickstart.md Tahap 0)

**Checkpoint**: Garis dasar hijau dan cuplikan pembanding tersimpan.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Memecah `BrandSettings` menjadi lima kelas settings dan memindahkan **40 berkas** yang merujuknya, tanpa menambah satu pun fitur.

**⚠️ CRITICAL**: Seluruh user story diblokir sampai fase ini selesai.

**⚠️ Ukuran keberhasilan fase ini adalah TIDAK ADANYA PERUBAHAN**: keluaran halaman publik identik dengan cuplikan T002 dan seluruh test lama hijau (FR-069, SC-011). Fase ini harus mendarat sebagai perubahan tersendiri, tidak dicampur dengan Phase 3.

### Kelas settings

- [ ] T003 [P] Buat `App\Settings\SiteSettings` di `app/Settings/SiteSettings.php` — grup `site`, 19 properti sesuai data-model.md §1, termasuk pemindahan metode `whatsappUrl(string $message): ?string` dari `BrandSettings`
- [ ] T004 [P] Buat `App\Settings\AppearanceSettings` di `app/Settings/AppearanceSettings.php` — grup `appearance`, 6 properti sesuai data-model.md §2, termasuk pemindahan konstanta `DEFAULT_PRIMARY_COLOR`, `DEFAULT_SECONDARY_COLOR`, `DEFAULT_FONT_HEADING`, `DEFAULT_FONT_BODY`, dan `FONT_OPTIONS`
- [ ] T005 [P] Buat `App\Settings\SeoSettings` di `app/Settings/SeoSettings.php` — grup `seo`, 22 properti sesuai data-model.md §3
- [ ] T006 [P] Buat `App\Settings\ScriptSettings` di `app/Settings/ScriptSettings.php` — grup `script`, 14 properti sesuai data-model.md §4
- [ ] T007 [P] Buat `App\Settings\SocialSettings` di `app/Settings/SocialSettings.php` — grup `social`, 10 properti sesuai data-model.md §5, termasuk pemindahan metode `ogImageUrl(): string` dari `BrandSettings`

### Settings migration

- [ ] T008 Buat settings migration di `database/settings/` yang menambahkan seluruh properti **baru** untuk kelima grup beserta nilai bawaannya sesuai kolom "Bawaan" pada data-model.md — kecuali 12 properti yang akan dipindahkan pada T009
- [ ] T009 Buat settings migration di `database/settings/` yang memindahkan 12 properti `brand.*` ke tujuan barunya memakai `$this->migrator->rename()` sesuai tabel peta perpindahan research.md R2; bungkus tiap pemindahan dengan pemeriksaan keberadaan agar migration tetap idempoten pada basis data yang sudah sebagian berpindah
- [ ] T010 Tulis test di `tests/Feature/Settings/BrandSettingsMigrationTest.php` yang membuktikan setiap nilai `brand.*` yang terisi berpindah utuh tanpa berubah isinya, dan grup `brand` tidak lagi menyimpan properti apa pun setelah migrasi (contracts/settings-application-contract.md §11, FR-068, FR-063)

### Pemindahan rujukan (40 berkas)

- [ ] T011 [P] Pindahkan rujukan pada controller publik ke kelas settings baru: `app/Http/Controllers/Public/CalculatorController.php`, `app/Http/Controllers/Public/CareerController.php`, `app/Http/Controllers/Public/ContactController.php`, `app/Http/Controllers/Public/SitemapController.php`
- [ ] T012 [P] Pindahkan rujukan pada mail dan notification: `app/Mail/CalculatorLeadThankYou.php`, `app/Mail/ContactSubmissionThankYou.php`, `app/Notifications/NewCalculatorLead.php`, `app/Notifications/NewContactSubmission.php`
- [ ] T013 [P] Pindahkan branding panel admin ke `AppearanceSettings` dan `SiteSettings` di `app/Providers/Filament/AdminPanelProvider.php`, termasuk helper `brandAssetUrl()`
- [ ] T014 [P] Pindahkan sumber data Organization ke `SiteSettings` dan `SocialSettings` di `app/Support/Seo/JsonLd.php` (FR-032 — tanpa menambah properti baru, data perusahaan tidak boleh punya dua tempat pengisian)
- [ ] T015 [P] Pindahkan rujukan pada layout dan partial: `resources/views/layouts/public.blade.php`, `resources/views/layouts/partials/og-meta.blade.php`, `resources/views/layouts/partials/theme-vars.blade.php`
- [ ] T016 [P] Pindahkan rujukan pada komponen layout dan section: `resources/views/components/layout/header.blade.php`, `resources/views/components/layout/footer.blade.php`, `resources/views/components/sections/cta-band.blade.php`, `resources/views/components/sections/team-members.blade.php`
- [ ] T017 [P] Pindahkan rujukan pada view halaman: `resources/views/pages/home.blade.php`, `faq.blade.php`, `karir.blade.php`, `kontak.blade.php`, `tentang-kami.blade.php`, `artikel/index.blade.php`, `artikel/show.blade.php`, `produk/index.blade.php`, `produk/show.blade.php`, `portfolio/index.blade.php`, `portfolio/show.blade.php`, `custom-page/show.blade.php`
- [ ] T018 [P] Pindahkan rujukan pada `database/seeders/MenuItemDemoSeeder.php` dan perbarui komentar perujuk `BrandSettings` di `resources/css/app.css`
- [ ] T019 Perbarui test lama yang merujuk `BrandSettings` ke kelas settings baru tanpa mengubah maksud pengujiannya: `tests/Feature/Admin/JobOpeningResourceTest.php`, `tests/Feature/Pages/ContactPageTest.php`, `tests/Feature/Public/CalculatorLeadTest.php`, `tests/Feature/Public/CareerModuleToggleTest.php`, `tests/Feature/Public/SeoGlobalMetaTest.php`, `tests/Feature/Public/SitemapTest.php`, `tests/Feature/Settings/OgMetaTagTest.php`
- [ ] T020 Ganti `tests/Feature/Settings/BrandSettingsTest.php` menjadi test untuk halaman pengaturan baru yang setara cakupannya; jangan hapus pertanggungan uji yang sudah ada tanpa penggantinya

### Pembubaran dan halaman admin

- [ ] T021 Hapus `app/Settings/BrandSettings.php` dan `app/Filament/Pages/BrandSettingsPage.php` beserta view `resources/views/filament/pages/brand-settings-page.blade.php` setelah T011 sampai T020 selesai; pastikan tidak ada kelas pembaca lama yang disisakan (FR-063, FR-064)
- [ ] T022 [P] Buat `app/Filament/Pages/SiteSettingsPage.php` dengan navigation group `Settings`, memuat seluruh properti `SiteSettings` termasuk setelan operasional (FR-070, FR-072)
- [ ] T023 [P] Buat `app/Filament/Pages/AppearanceSettingsPage.php` memuat warna, font, logo, dan favicon (FR-070, FR-071)
- [ ] T024 [P] Buat `app/Filament/Pages/SeoSettingsPage.php` dengan section bertab sesuai kelompok pada data-model.md §3 (FR-070)
- [ ] T025 [P] Buat `app/Filament/Pages/SocialSettingsPage.php` memuat profil, tombol berbagi, dan gambar berbagi default (FR-070)
- [ ] T026 Buat `app/Filament/Pages/ScriptSettingsPage.php` dengan `canAccess()` dan `shouldRegisterNavigation()` yang memeriksa peran `super_admin`, mengikuti pola `app/Providers/Filament/AdminPanelProvider.php:84` (FR-045, research.md R3) — pembatasan dipasang bersamaan dengan pembuatan halaman, tidak ditunda
- [ ] T027 Tulis test di `tests/Feature/Settings/SettingsPagesAccessTest.php` yang membuktikan kelima halaman dapat dibuka super admin, tiap halaman dapat disimpan sendiri tanpa mengubah nilai halaman lain, halaman Scripts & Analytics tidak terlihat bagi admin biasa, dan membuka alamatnya langsung ditolak (contracts §10, FR-070, FR-045)

### Pembuktian tanpa perubahan

- [ ] T028 Jalankan `php artisan migrate` lalu bandingkan keluaran halaman publik dengan cuplikan T002 — tidak boleh ada selisih (FR-069, SC-011, quickstart.md Tahap 0)
- [ ] T029 Jalankan `vendor/bin/pint --dirty --format agent` dan `php artisan test --compact`; seluruh test harus hijau tanpa pengecualian sebelum melanjutkan ke Phase 3

**Checkpoint**: Fondasi siap. Situs tidak berubah sedikit pun, `BrandSettings` sudah tiada, lima halaman pengaturan tersedia. User story dapat dimulai.

---

## Phase 3: User Story 1 - Identitas dan kontak situs berhenti ditulis mati di kode (Priority: P1) 🎯 MVP

**Goal**: Nama, kontak perusahaan, teks hak cipta, dan tautan legal sepenuhnya berasal dari pengaturan, sehingga tidak ada lagi data klien lain di footer.

**Independent Test**: Isi seluruh informasi perusahaan dan legal dengan data berbeda dari bawaan, buka halaman publik, verifikasi footer memakai data baru dan penelusuran keluaran tidak menemukan jejak data contoh bawaan.

### Tests for User Story 1

> Tulis test lebih dulu dan pastikan GAGAL sebelum implementasi.

- [ ] T030 [P] [US1] Tulis test di `tests/Feature/Public/FooterCompanyInfoTest.php` — nama, email, telepon, dan alamat perusahaan tampil di blok kontak footer; tiap baris yang nilainya kosong tidak dirender tanpa menyisakan ikon atau label menggantung (contracts §3, FR-003, FR-004)
- [ ] T031 [P] [US1] Tulis test di `tests/Feature/Public/FooterLegalSettingsTest.php` — teks hak cipta dan ketiga tautan legal memakai pengaturan, dan tautan yang alamatnya kosong tidak dirender (contracts §3, FR-007)
- [ ] T032 [P] [US1] Tulis test di `tests/Feature/Public/NoHardcodedClientDataTest.php` — keluaran halaman publik pada instalasi bersih tidak memuat penanda data contoh klien tertentu (contracts §3, FR-009, SC-002)
- [ ] T033 [P] [US1] Tulis test di `tests/Feature/Public/SiteIdentityTest.php` — atribut bahasa dokumen mengikuti `default_language`, dan nama situs dipakai pada judul serta `og:site_name` (contracts §1, FR-001, FR-005)

### Implementation for User Story 1

- [ ] T034 [US1] Ganti blok kontak yang ditulis mati di `resources/views/components/layout/footer.blade.php` dengan nilai dari `SiteSettings`, sembunyikan tiap baris yang kosong (FR-003, FR-004)
- [ ] T035 [US1] Ganti teks hak cipta dan ketiga tautan legal yang ditulis mati di `resources/views/components/layout/footer.blade.php` dengan nilai dari `SiteSettings`, sembunyikan tautan yang alamatnya kosong (FR-007)
- [ ] T036 [US1] Ganti `lang="id"` yang ditulis mati di `resources/views/layouts/public.blade.php` dengan `default_language` dari `SiteSettings` (FR-005)
- [ ] T037 [US1] Terapkan zona waktu dari `SiteSettings` sebagai acuan tampilan tanggal pada halaman publik (FR-005)
- [ ] T038 [US1] Tambahkan nilai bawaan yang wajar dan netral untuk seluruh properti identitas, perusahaan, dan legal sehingga instalasi baru tampil utuh tanpa data klien mana pun (FR-008, FR-009)
- [ ] T039 [US1] Tambahkan validasi pada `SiteSettingsPage`: email berformat email, bahasa dan zona waktu dari daftar yang disediakan, serta batas panjang pada deskripsi dan pesan agar tata letak tidak rusak (spec.md Edge Cases)
- [ ] T040 [US1] Jalankan test US1 dan pastikan seluruhnya hijau

**Checkpoint**: Identitas dan kontak situs sepenuhnya dapat diatur admin. Data klien lain hilang dari keluaran.

---

## Phase 4: User Story 2 - Ikon media sosial berhenti menjadi tautan mati (Priority: P1)

**Goal**: Ikon sosial di header menuju profil yang diisi admin, dan ikon tanpa URL tidak ditampilkan.

**Independent Test**: Isi dua alamat profil dan kosongkan sisanya, buka halaman publik, verifikasi hanya dua ikon tampil dan keduanya menuju alamat yang diisi.

### Tests for User Story 2

- [ ] T041 [P] [US2] Tulis test di `tests/Feature/Public/SocialProfileLinksTest.php` — ikon platform terisi menuju URL-nya, ikon platform kosong tidak dirender, seluruh kelompok ikon hilang bila tidak ada URL terisi, dan tidak ada ikon bertautan kosong tersisa (contracts §2, FR-019, FR-020, SC-005)
- [ ] T042 [P] [US2] Tulis test di `tests/Feature/Settings/SocialSettingsValidationTest.php` — URL profil yang bukan alamat web sah ditolak dengan pesan yang menyebut platformnya (FR-021)

### Implementation for User Story 2

- [ ] T043 [US2] Ganti larik `$socials` yang ditulis mati dengan `href => '#'` di `resources/views/components/layout/header.blade.php` agar memakai URL dari `SocialSettings`, dan sembunyikan ikon yang URL-nya kosong (FR-019, FR-020)
- [ ] T044 [US2] Sembunyikan seluruh kelompok ikon sosial di `resources/views/components/layout/header.blade.php` bila tidak ada satu pun URL terisi, tanpa menyisakan ruang kosong (FR-020)
- [ ] T045 [US2] Tambahkan ikon untuk platform yang belum punya lambang di header — LinkedIn, Pinterest, dan TikTok — di `resources/views/components/layout/header.blade.php` (spec.md US2 skenario 5)
- [ ] T046 [US2] Tambahkan validasi URL per platform pada `app/Filament/Pages/SocialSettingsPage.php` dengan pesan yang menyebut platformnya (FR-021)
- [ ] T047 [US2] Sambungkan daftar profil terisi sebagai `sameAs` pada `app/Support/Seo/JsonLd.php` (FR-032, contracts §1)
- [ ] T048 [US2] Jalankan test US2 dan pastikan seluruhnya hijau

**Checkpoint**: Tidak ada lagi ikon sosial bertautan mati di situs.

---

## Phase 5: User Story 3 - Pemasangan kode pelacakan tanpa menyentuh kode (Priority: P1)

**Goal**: Super admin dapat memasang kode pihak ketiga dan penyesuaian tampilan tanpa rilis kode baru.

**Independent Test**: Isi keempat slot beserta CSS dan JS khusus dengan penanda unik, verifikasi tiap penanda berada tepat pada posisinya di halaman publik, tidak ada yang muncul di panel admin, dan admin biasa tidak dapat membuka halaman pengaturannya.

### Tests for User Story 3

- [ ] T049 [P] [US3] Tulis test di `tests/Feature/Public/ScriptSlotRenderingTest.php` — isi keempat slot muncul tepat pada posisi yang dijanjikan labelnya, dimuat apa adanya sebagai kode dan tidak di-escape (contracts §4, FR-041, FR-044)
- [ ] T050 [P] [US3] Tulis test di `tests/Feature/Public/CustomCssJsTest.php` — CSS khusus dimuat di kepala dokumen dan JS khusus dimuat sebelum badan ditutup (contracts §4, FR-042, FR-043)
- [ ] T051 [P] [US3] Tulis test di `tests/Feature/Public/ScriptSlotEmptyStateTest.php` — slot kosong tidak meninggalkan elemen kosong pada halaman (contracts §4, FR-047)
- [ ] T052 [P] [US3] Tulis test di `tests/Feature/Admin/ScriptSlotNotInAdminTest.php` — tidak ada satu pun isi slot yang dimuat di dalam panel admin (contracts §4, FR-046)

### Implementation for User Story 3

- [ ] T053 [US3] Tambahkan titik pemuatan keempat slot di `resources/views/layouts/public.blade.php`: di dalam `<head>`, tepat setelah `<body>` dibuka, tepat sebelum `</body>`, dan pada bagian footer (FR-041)
- [ ] T054 [US3] Tambahkan blok gaya untuk `custom_css` di kepala dokumen dan blok skrip untuk `custom_js` sebelum badan ditutup di `resources/views/layouts/public.blade.php` (FR-042, FR-043)
- [ ] T055 [US3] Pastikan setiap titik pemuatan hanya dirender bila slotnya terisi, sehingga tidak ada elemen kosong tersisa (FR-047)
- [ ] T056 [US3] Beri komentar pada setiap titik pemuatan yang menyebut FR-044 dan FR-045, menjelaskan bahwa keluaran tanpa escaping adalah keputusan sadar beserta pengamannya, agar peninjau berikutnya tidak mengubahnya menjadi keluaran ter-escape dan mematikan fitur (research.md R7)
- [ ] T057 [US3] Bangun form `app/Filament/Pages/ScriptSettingsPage.php` memakai `Textarea` bergaya monospace tanpa menambah dependensi editor kode (research.md R4)
- [ ] T058 [US3] Tambahkan batas ukuran per slot beserta pesan yang menyebut batasnya saat terlampaui di `app/Filament/Pages/ScriptSettingsPage.php` (FR-048)
- [ ] T059 [US3] Pastikan slot tidak pernah dimuat pada layout panel admin (FR-046)
- [ ] T060 [US3] Jalankan test US3 dan pastikan seluruhnya hijau

**Checkpoint**: Permintaan pemasangan pixel iklan dan Tag Manager tidak lagi memerlukan rilis kode. Tiga story P1 selesai — siap dirilis sebagai MVP.

---

## Phase 6: User Story 4 - SEO situs dapat disesuaikan per klien (Priority: P2)

**Goal**: Pola judul, kata kunci, kontrol pengindeksan, verifikasi situs, dan meta tambahan dapat diatur tanpa merusak SEO per konten yang sudah berjalan.

**Independent Test**: Ubah pemisah dan pola judul lalu periksa beberapa jenis halaman; isi satu kode verifikasi dan periksa penandanya; matikan izin pengindeksan dan periksa pernyataan halaman.

### Tests for User Story 4

- [ ] T061 [P] [US4] Tulis test di `tests/Unit/PageTitleTest.php` — penggantian penanda `{page_title}`, `{site_name}`, `{separator}`; penanda tak dikenal dibuang; pemisah menggantung dan spasi ganda dirapikan (FR-027)
- [ ] T062 [P] [US4] Tulis test di `tests/Feature/Public/SeoTitleFormatTest.php` — pola default dipakai halaman tanpa pola sendiri, pola per jenis halaman menang atasnya, dan judul SEO per konten menang atas keduanya (contracts §1, FR-025, FR-026, FR-028)
- [ ] T063 [P] [US4] Tulis test di `tests/Feature/Public/SeoIndexingControlTest.php` — mematikan izin pengindeksan dan izin ikut tautan tercermin pada pernyataan halaman (contracts §1, FR-030)
- [ ] T064 [P] [US4] Tulis test di `tests/Feature/Public/SeoVerificationTagTest.php` — penanda verifikasi hadir untuk tiap mesin pencari yang kodenya diisi dan tidak hadir bila kosong (contracts §1, FR-034)
- [ ] T065 [P] [US4] Tulis test di `tests/Feature/Public/SeoHeadExtrasTest.php` — kata kunci, kanonik default, `twitter:site`, dan meta tambahan hadir sesuai pengaturan (contracts §1, FR-029, FR-031, FR-033)

### Implementation for User Story 4

- [ ] T066 [US4] Buat `App\Support\Seo\PageTitle` di `app/Support/Seo/PageTitle.php` — menyusun judul dari pola, mengganti penanda yang dikenali, membuang penanda tak dikenal, lalu merapikan pemisah dan spasi (FR-025, FR-027, research.md R9)
- [ ] T067 [US4] Sambungkan `PageTitle` ke `resources/views/layouts/public.blade.php` sebagai lapisan default di bawah `meta_title` per konten dari `app/Concerns/HasSeoMetadata.php`, tanpa mengubah perilaku SEO per konten (FR-028)
- [ ] T068 [US4] Tambahkan pengaturan pola judul per jenis halaman pada `app/Filament/Pages/SeoSettingsPage.php`, dibatasi 12 jenis halaman yang benar-benar punya alamat publik sesuai data-model.md §3 (FR-026, research.md R0)
- [ ] T069 [US4] Buat `resources/views/layouts/partials/head-extra.blade.php` memuat pernyataan pengindeksan, kode verifikasi keempat mesin pencari, dan meta tambahan bebas (FR-030, FR-033, FR-034)
- [ ] T070 [US4] Sertakan `head-extra` pada `resources/views/layouts/public.blade.php` dan pindahkan sumber deskripsi, kanonik, serta kata kunci ke `SeoSettings` (FR-029)
- [ ] T071 [US4] Perbarui `resources/views/layouts/partials/og-meta.blade.php` agar memakai `twitter_handle` dari `SeoSettings` dan gambar berbagi default dari `SocialSettings` (FR-031, FR-024)
- [ ] T072 [US4] Jalankan test US4 dan pastikan seluruhnya hijau

**Checkpoint**: SEO dapat disesuaikan per klien tanpa merusak fondasi spec 014.

---

## Phase 7: User Story 5 - Aturan perayapan dan peta situs dapat diatur admin (Priority: P2)

**Goal**: Isi `robots.txt` dan komposisi `sitemap.xml` berasal dari pengaturan, bukan dari string yang ditulis mati.

**Independent Test**: Ubah isi aturan perayapan dan buka alamatnya; matikan satu jenis konten lalu periksa peta situs.

### Tests for User Story 5

- [ ] T073 [P] [US5] Tulis test di `tests/Feature/Public/RobotsTxtSettingsTest.php` — isi sesuai yang disimpan, penanda `{site_url}` tergantikan alamat aktif, dan aturan bawaan aman tersaji saat isi dikosongkan (contracts §5, FR-035, FR-036, FR-037)
- [ ] T074 [P] [US5] Tulis test di `tests/Feature/Public/SitemapSettingsTest.php` — jenis konten yang sakelarnya mati tidak tercantum sementara jenis lain tetap ada, peta situs menyatakan tidak tersedia saat dimatikan, dan tiap entri mencantumkan frekuensi serta prioritas default (contracts §6, FR-038, FR-039)
- [ ] T075 [P] [US5] Tulis test di `tests/Feature/Public/RobotsSitemapConsistencyTest.php` — baris `Sitemap:` tidak tersaji saat peta situs dimatikan meski admin menuliskannya (contracts §5, FR-040)

### Implementation for User Story 5

- [ ] T076 [US5] Ubah `SitemapController::robots()` di `app/Http/Controllers/Public/SitemapController.php` agar membaca isi dari `SeoSettings`, mengganti penanda `{site_url}`, dan jatuh ke aturan bawaan aman saat kosong (FR-035, FR-036, FR-037)
- [ ] T077 [US5] Ubah `SitemapController::xml()` di `app/Http/Controllers/Public/SitemapController.php` agar menyaring jenis konten sesuai sakelar dan mengembalikan penolakan saat peta situs dimatikan (FR-038)
- [ ] T078 [US5] Tambahkan frekuensi perubahan dan prioritas default pada tiap entri di `resources/views/sitemap.blade.php` (FR-039)
- [ ] T079 [US5] Jaga keselarasan aturan perayapan, peta situs, dan kontrol pengindeksan sehingga tidak saling bertentangan maupun menunjuk berkas yang tidak tersedia (FR-040, research.md R10)
- [ ] T080 [US5] Jalankan test US5 dan pastikan seluruhnya hijau

**Checkpoint**: Klien dapat mengatur perayapan sendiri tanpa meminta rilis baru.

---

## Phase 8: User Story 6 - Pengunjung mengendalikan persetujuan cookie (Priority: P2)

**Goal**: Skrip analitik dan pemasaran baru berjalan setelah pengunjung menyetujui kategorinya.

**Dependency**: Memerlukan slot skrip dari US3 (Phase 5), karena yang ditahan persetujuan adalah slot tersebut.

**Independent Test**: Aktifkan persetujuan dan tandai satu slot sebagai analitik; verifikasi slot tidak berjalan sebelum disetujui, berjalan setelah disetujui, dan pilihan diingat pada kunjungan berikutnya.

### Tests for User Story 6

- [ ] T081 [P] [US6] Tulis test di `tests/Feature/Public/CookieConsentBannerTest.php` — bilah tampil saat persetujuan aktif, memuat tombol terima dan tolak serta tautan pengaturan kategori, dan tidak tampil beserta tautan footernya saat persetujuan dimatikan (contracts/consent-gating-contract.md §2 dan §3, FR-051, FR-057, FR-073, FR-074, FR-075)
- [ ] T082 [P] [US6] Tulis test di `tests/Feature/Public/ConsentGatedScriptTest.php` — slot bertanda analitik atau pemasaran dirender dalam bentuk yang tidak dieksekusi peramban, sementara slot bertanda `none` dirender sebagai skrip biasa (contracts/consent-gating-contract.md §5, FR-054)
- [ ] T083 [P] [US6] Tulis test di `tests/Feature/Public/CookieConsentFooterLinkTest.php` — tautan pengaturan cookie hadir di footer sebaris tautan legal hanya saat persetujuan diaktifkan (contracts §3, FR-056, FR-075)

### Implementation for User Story 6

- [ ] T084 [US6] Tambahkan penandaan kategori persetujuan per slot pada `app/Filament/Pages/ScriptSettingsPage.php` dengan pilihan `none`, `analytics`, dan `marketing` (FR-053)
- [ ] T085 [US6] Ubah titik pemuatan slot di `resources/views/layouts/public.blade.php` agar slot berkategori dirender dalam bentuk yang tidak dieksekusi peramban sampai kategorinya disetujui (FR-054, research.md R8)
- [ ] T086 [US6] Buat `resources/views/components/layout/cookie-consent.blade.php` — bilah non-blokir dengan tombol terima dan tolak berpenonjolan setara serta tautan pengaturan kategori (FR-073, FR-074)
- [ ] T087 [US6] Terapkan penyimpanan keputusan pada perangkat pengunjung memakai Alpine.js yang sudah dipakai project ini, tanpa dependensi baru; perlakukan penyimpanan yang tidak tersedia atau kosong sebagai belum memilih (FR-055, FR-058, contracts/consent-gating-contract.md §6)
- [ ] T088 [US6] Aktifkan slot yang disetujui tanpa memuat ulang halaman, dan pastikan kegagalan skrip tidak menghalangi isi halaman tampil maupun dinavigasi (FR-049, contracts/consent-gating-contract.md §5)
- [ ] T089 [US6] Tambahkan tautan pengaturan cookie di `resources/views/components/layout/footer.blade.php` sebaris tautan legal, hanya dirender saat persetujuan diaktifkan (FR-056, FR-075)
- [ ] T090 [US6] Pastikan kategori yang diperlukan agar situs berfungsi selalu aktif dan tidak dapat dimatikan pengunjung (FR-052)
- [ ] T091 [US6] Jalankan test US6 dan pastikan seluruhnya hijau

**Checkpoint**: Pelacakan berjalan hanya setelah pengunjung menyetujuinya.

---

## Phase 9: User Story 7 - Situs dapat ditutup sementara saat pemeliharaan (Priority: P3)

**Goal**: Admin dapat menutup situs bagi pengunjung sambil tetap memakai panel.

**Independent Test**: Nyalakan mode pemeliharaan, periksa halaman publik sebagai pengunjung anonim, lalu pastikan panel admin tetap dapat diakses.

### Tests for User Story 7

- [ ] T092 [P] [US7] Tulis test di `tests/Feature/Public/MaintenanceModeTest.php` — pengunjung anonim menerima halaman pemeliharaan dengan status yang menyatakan kondisi sementara, panel admin tetap dapat diakses, pengguna terautentikasi tetap melihat isi situs, dan situs normal kembali setelah sakelar dimatikan (contracts §7, FR-011 sampai FR-013)

### Implementation for User Story 7

- [ ] T093 [US7] Buat `App\Http\Middleware\MaintenanceMode` di `app/Http/Middleware/MaintenanceMode.php` yang membaca `SiteSettings`, mengembalikan halaman pemeliharaan dengan status sementara, dan melewatkan pengguna terautentikasi (FR-011, FR-012, FR-013, research.md R5)
- [ ] T094 [US7] Daftarkan middleware pada grup rute publik saja di `bootstrap/app.php`, tanpa menyentuh rute panel admin (FR-013)
- [ ] T095 [US7] Buat `resources/views/maintenance.blade.php` memuat identitas situs dan pesan pemeliharaan (FR-011)
- [ ] T096 [US7] Tambahkan penanda jelas pada panel admin selama mode pemeliharaan aktif (FR-014)
- [ ] T097 [US7] Jalankan test US7 dan pastikan seluruhnya hijau

**Checkpoint**: Situs dapat ditutup dan dibuka sepenuhnya dari panel.

---

## Phase 10: User Story 8 - Halaman kesalahan berbicara dengan bahasa klien (Priority: P3)

**Goal**: Pesan pada halaman tidak ditemukan dan gangguan sistem mengikuti nada bicara klien.

**Independent Test**: Isi pesan 404, buka alamat yang tidak ada, verifikasi pesan tersebut tampil beserta jalan kembali.

### Tests for User Story 8

- [ ] T098 [P] [US8] Tulis test di `tests/Feature/Public/CustomErrorPageTest.php` — alamat tidak ada menampilkan pesan dari pengaturan, pesan bawaan tampil saat pengaturan kosong, halaman memuat identitas situs dan jalan kembali, serta tidak membocorkan rincian teknis (contracts §8, FR-015 sampai FR-017)

### Implementation for User Story 8

- [ ] T099 [P] [US8] Buat `resources/views/errors/404.blade.php` memakai layout publik dan pesan dari `SiteSettings` dengan fallback bawaan (FR-015, FR-016)
- [ ] T100 [P] [US8] Buat `resources/views/errors/500.blade.php` yang tahan gagal — tetap tampil dengan pesan bawaan bila pengaturan tidak dapat dibaca (FR-017, research.md R6)
- [ ] T101 [US8] Jalankan test US8 dan pastikan seluruhnya hijau

**Checkpoint**: Halaman kesalahan tidak lagi memakai tampilan bawaan framework.

---

## Phase 11: User Story 9 - Pengunjung membagikan konten ke media sosial (Priority: P3)

**Goal**: Tombol berbagi tersedia pada halaman artikel dan produk sesuai platform pilihan admin.

**Independent Test**: Nyalakan tombol berbagi dan pilih dua platform, buka satu artikel, verifikasi tepat dua tombol tampil dan membawa alamat artikel tersebut.

### Tests for User Story 9

- [ ] T102 [P] [US9] Tulis test di `tests/Feature/Public/SocialShareButtonsTest.php` — hanya platform terpilih yang dirender pada artikel dan produk, tiap tombol membawa alamat konten yang sedang dibuka, dan tidak ada tombol dirender saat sakelar dimatikan (contracts §9, FR-022, FR-023)

### Implementation for User Story 9

- [ ] T103 [US9] Buat `resources/views/components/layout/social-share.blade.php` yang menerima judul dan alamat konten lalu merender hanya platform terpilih dari `SocialSettings` (FR-022, FR-023, research.md R13)
- [ ] T104 [US9] Pasang komponen berbagi pada `resources/views/pages/artikel/show.blade.php` dan `resources/views/pages/produk/show.blade.php` (FR-023)
- [ ] T105 [US9] Jalankan test US9 dan pastikan seluruhnya hijau

**Checkpoint**: Seluruh sembilan user story selesai dan dapat diuji mandiri.

---

## Phase 12: Polish & Cross-Cutting Concerns

- [ ] T106 Telusuri seluruh halaman pengaturan dan pastikan tidak ada dua field yang mengatur nilai yang sama (FR-064, SC-012)
- [ ] T107 [P] Perbarui dokumentasi deployment dan checklist go-live spec 021 agar memuat kelima halaman pengaturan baru — Deployment Standards konstitusi menganggap modul tak terdokumentasi sebagai belum selesai (plan.md Constitution Check)
- [ ] T108 [P] Perbarui `database/seeders/` bila ada nilai bawaan pengaturan yang perlu di-seed untuk instalasi baru, tanpa memasukkan konten demo ke `DatabaseSeeder` (Deployment Standards)
- [ ] T109 Periksa waktu muat halaman publik dengan seluruh pengaturan terisi wajar dan bandingkan dengan garis dasar T002 (SC-009)
- [ ] T110 Jalankan seluruh langkah verifikasi manual pada [quickstart.md](./quickstart.md) dari Tahap 0 sampai Tahap 3
- [ ] T111 Jalankan `vendor/bin/pint --dirty --format agent` lalu `php artisan test --compact` dan pastikan seluruh test hijau

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tanpa dependensi
- **Foundational (Phase 2)**: Bergantung pada Phase 1 — **MEMBLOKIR seluruh user story**
- **US1, US2, US3 (Phase 3 sampai 5)**: Bergantung pada Phase 2, saling independen
- **US4, US5 (Phase 6 dan 7)**: Bergantung pada Phase 2, saling independen
- **US6 (Phase 8)**: Bergantung pada Phase 2 **dan US3** — yang ditahan persetujuan adalah slot skrip dari US3
- **US7, US8, US9 (Phase 9 sampai 11)**: Bergantung pada Phase 2, saling independen
- **Polish (Phase 12)**: Bergantung pada seluruh story yang dikerjakan

### Satu-satunya dependensi antar story

US6 memerlukan US3. Delapan story lainnya hanya memerlukan fondasi Phase 2.

### Parallel Opportunities

- T003 sampai T007 (lima kelas settings) paralel penuh — berkas berbeda
- T011 sampai T018 (pemindahan rujukan) paralel — kelompok berkas berbeda, tetapi seluruhnya harus selesai sebelum T021 menghapus `BrandSettings`
- T022 sampai T025 (empat halaman admin) paralel; T026 terpisah karena memuat pembatasan akses
- Seluruh task test bertanda [P] dalam satu story paralel
- Setelah Phase 2, US1 sampai US5 dan US7 sampai US9 dapat dikerjakan paralel oleh orang berbeda

---

## Parallel Example: Phase 2 Foundational

```bash
# Lima kelas settings sekaligus:
Task: "Buat App\Settings\SiteSettings di app/Settings/SiteSettings.php"
Task: "Buat App\Settings\AppearanceSettings di app/Settings/AppearanceSettings.php"
Task: "Buat App\Settings\SeoSettings di app/Settings/SeoSettings.php"
Task: "Buat App\Settings\ScriptSettings di app/Settings/ScriptSettings.php"
Task: "Buat App\Settings\SocialSettings di app/Settings/SocialSettings.php"

# Pemindahan rujukan per kelompok berkas:
Task: "Pindahkan rujukan pada controller publik"
Task: "Pindahkan rujukan pada mail dan notification"
Task: "Pindahkan rujukan pada layout dan partial"
Task: "Pindahkan rujukan pada view halaman"
```

---

## Implementation Strategy

### MVP: tiga story P1

Spec ini besar. MVP yang bermakna adalah **Phase 1 + Phase 2 + Phase 3 sampai 5**, karena ketiganya bersama-sama menghapus data klien lain dari situs, memperbaiki ikon bertautan mati, dan menjawab permintaan awal pemasangan kode pelacakan.

1. Selesaikan Phase 1 dan Phase 2 — **berhenti dan validasi**: situs tidak berubah, seluruh test hijau
2. Selesaikan Phase 3 (US1) — berhenti dan validasi
3. Selesaikan Phase 4 (US2) — berhenti dan validasi
4. Selesaikan Phase 5 (US3) — berhenti dan validasi, **siap dirilis**

### Peringatan tentang Phase 2

Phase 2 menyentuh 40 berkas tanpa memberi fitur baru. Ini fase paling berisiko sekaligus paling mudah diremehkan. Dua aturan yang menjaganya:

- Mendarat sebagai perubahan tersendiri, tidak dicampur Phase 3
- Diterima hanya bila keluaran halaman publik identik dengan garis dasar T002 dan seluruh test lama hijau

### Pengiriman bertahap setelah MVP

Tambahkan US4 dan US5, lalu US6 (memerlukan US3), lalu US7 sampai US9. Setiap story menambah nilai tanpa merusak story sebelumnya.

---

## Notes

- Task [P] = berkas berbeda, tanpa dependensi
- Label [Story] memetakan task ke user story untuk keterlacakan
- Test ditulis lebih dulu dan harus GAGAL sebelum implementasi
- Jalankan `vendor/bin/pint --dirty --format agent` setiap selesai menyentuh berkas PHP
- Commit setiap task atau kelompok logis
- Hindari: menghapus test lama tanpa penggantinya, mencampur Phase 2 dengan Phase 3, mengubah keluaran tanpa escaping pada slot kode menjadi ter-escape
