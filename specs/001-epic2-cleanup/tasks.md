---

description: "Task list for Rapikan Epic 2 - Auth, White-labeling & Audit Trail"
---

# Tasks: Rapikan Epic 2 - Auth, White-labeling & Audit Trail

**Input**: Design documents from `/specs/001-epic2-cleanup/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/admin-panel-surface.md](./contracts/admin-panel-surface.md), [quickstart.md](./quickstart.md)

**Tests**: Disertakan — Constitution Principle IV (Module Test Coverage) mewajibkan feature test dasar sebelum sebuah unit kerja dianggap selesai.

**Organization**: Tasks dikelompokkan per user story (US1/US2/US3, sesuai prioritas P1/P2/P3 di spec.md) supaya masing-masing bisa dikerjakan, ditest, dan dirilis independen.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak saling bergantung)
- **[Story]**: User story terkait (US1, US2, US3)
- Path file selalu eksplisit

## Path Conventions

Single Laravel + Filament application (lihat [plan.md](./plan.md) § Project Structure) — semua path relatif ke root repo.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Tidak ada infrastruktur baru yang dipakai bersama ketiga story (US1 & US2 memakai package yang sudah terinstall; hanya US3 butuh package baru — lihat Foundational). Phase ini hanya verifikasi baseline.

- [X] T001 Jalankan `vendor/bin/pint --format agent` dan `php artisan test --compact` untuk memastikan baseline hijau sebelum mulai (tidak ada file diubah di task ini)

**Checkpoint**: Baseline project terverifikasi bersih sebelum perubahan dimulai.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Prasyarat yang memblokir US3 (Activity Log) saja — US1 dan US2 tidak punya prasyarat bersama dan bisa langsung dikerjakan setelah Phase 1.

**⚠️ CRITICAL**: T002–T004 harus selesai sebelum task apa pun di Phase 5 (US3) dimulai.

- [X] T002 Tambahkan `spatie/laravel-activitylog:^4.12` dan `rmsramos/activitylog:^2.0` ke `composer.json` lalu `composer update spatie/laravel-activitylog rmsramos/activitylog` (versi dipilih sesuai research.md §3 — bukan v5/v3/v4 karena tidak kompatibel PHP 8.3 / Filament 3.2)
- [X] T003 Publish migration & config activitylog: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"` dan `--tag="activitylog-config"`, lalu jalankan `php artisan migrate`
- [X] T004 Set `delete_records_older_than_days` ke `90` di `config/activitylog.php` (FR-009)

**Checkpoint**: Tabel `activity_log` siap dipakai — US3 bisa mulai dikerjakan.

---

## Phase 3: User Story 1 - Admin melihat ringkasan traffic website di dashboard (Priority: P1) 🎯 MVP

**Goal**: Admin melihat widget traffic (visitor, pageviews, top pages, sumber traffic) langsung di dashboard `/admin` menggunakan plugin GA yang sudah terinstall, dengan pesan jelas saat kredensial belum siap.

**Independent Test**: Login sebagai admin dengan kredensial GA4 valid, buka `/admin`, verifikasi 4 widget tampil dengan data nyata. Kosongkan `ANALYTICS_PROPERTY_ID`, reload, verifikasi pesan informatif muncul (bukan exception/500).

### Tests for User Story 1

- [X] T005 [P] [US1] Feature test: dashboard menampilkan widget GA saat kredensial valid, di `tests/Feature/Dashboard/GoogleAnalyticsWidgetTest.php`
- [X] T006 [P] [US1] Feature test: dashboard menampilkan pesan informatif (bukan HTTP 500) saat `ANALYTICS_PROPERTY_ID` kosong, di `tests/Feature/Dashboard/GoogleAnalyticsWidgetTest.php`

### Implementation for User Story 1

- [X] T007 [US1] Publish `config/filament-google-analytics.php` via `php artisan vendor:publish --tag=filament-google-analytics-config`
- [X] T008 [US1] Di `config/filament-google-analytics.php`, set widget `visitors`, `page_views`, `most_visited_pages`, `top_referrers_list` → `filament_dashboard = true`; widget breakdown lain (country/device/duration/active-users) → `filament_dashboard = false` supaya dashboard default tidak penuh (tetap tersedia di dashboard khusus plugin, sesuai FR-010 dan research.md §1)
- [X] T009 [US1] Buat helper/service kecil `app/Services/Analytics/AnalyticsAvailability.php` dengan method `isConfigured(): bool` yang memvalidasi `config('analytics.property_id')` terisi DAN file `config('analytics.service_account_credentials_json')` ada (FR-002)
- [X] T010 [US1] Buat `app/Filament/Pages/Dashboard.php` extends `\Filament\Pages\Dashboard`, override `getHeaderWidgets()`/render untuk menampilkan banner/notification Filament ("Analytics belum dikonfigurasi") ketika `AnalyticsAvailability::isConfigured()` bernilai false, memakai widget GA seperti biasa ketika true (FR-001, FR-002)
- [X] T011 [US1] Daftarkan `app/Filament/Pages/Dashboard.php` sebagai override dashboard di `app/Providers/Filament/AdminPanelProvider.php` (`->pages([Pages\Dashboard::class])`, hapus default jika perlu sesuai dokumentasi Filament di research.md)

**Checkpoint**: User Story 1 selesai — dashboard admin menampilkan traffic GA secara mandiri, bisa dites dan dirilis tanpa menunggu US2/US3.

---

## Phase 4: User Story 2 - Admin panel tidak membawa branding bawaan starter kit ke klien (Priority: P2)

**Goal**: Nama, logo, favicon, dan warna primer admin panel bisa dikonfigurasi per instalasi lewat halaman Settings, tanpa redeploy.

**Independent Test**: Isi Brand Settings (nama, upload logo, upload favicon, pilih warna), simpan, reload `/admin`, verifikasi seluruh elemen branding berubah. Hapus konfigurasi, verifikasi fallback ke default yang wajar.

### Tests for User Story 2

- [X] T012 [P] [US2] Feature test: menyimpan Brand Settings (nama, warna) lewat halaman admin, di `tests/Feature/Settings/BrandSettingsTest.php`
- [X] T013 [P] [US2] Feature test: admin panel menampilkan nama/warna default yang wajar ketika Brand Settings belum diisi (FR-005), di `tests/Feature/Settings/BrandSettingsTest.php`

### Implementation for User Story 2

- [X] T014 [P] [US2] Buat `app/Settings/BrandSettings.php` (kelas Settings Spatie) dengan properti `app_name`, `primary_color` sesuai skema di [data-model.md](./data-model.md#brand-settings)
- [X] T015 [US2] Buat migration settings (`php artisan make:settings-migration CreateBrandSettings` atau setara) untuk properti di T014, lalu `php artisan migrate` (depends on T014)
- [X] T016 [P] [US2] Tambahkan media collection `brand-logo` dan `brand-favicon` (Spatie Media Library, single-file) pada `BrandSettings` atau model pendamping untuk menyimpan file logo/favicon dengan validasi tipe file gambar (edge case di spec.md)
- [X] T017 [US2] Buat halaman Filament Settings `app/Filament/Pages/BrandSettingsPage.php` dengan form: text input nama, FileUpload logo, FileUpload favicon, ColorPicker warna primer (depends on T014, T016)
- [X] T018 [US2] Update `app/Providers/Filament/AdminPanelProvider.php`: `->brandName()`, `->favicon()`, `->colors(['primary' => ...])` memakai closure yang membaca `app(BrandSettings::class)` dengan fallback ke `config('app.name')` / `Color::Indigo` (FR-003, FR-004, FR-005 — depends on T014)
- [X] T019 [US2] Update `app/Providers/Filament/AdminPanelProvider.php`: `->brandLogo()` memakai URL media dari `BrandSettings` (depends on T016, T018)

**Checkpoint**: User Story 2 selesai — branding admin panel sepenuhnya config-driven, independen dari US1/US3.

---

## Phase 5: User Story 3 - Melacak siapa mengubah apa di admin panel (Priority: P3)

**Goal**: Perubahan data oleh admin tercatat di activity log, hanya bisa dilihat Super Admin, dengan retensi 90 hari.

**Independent Test**: Ubah role user sebagai admin, buka halaman activity log sebagai Super Admin, verifikasi entri tercatat dengan before/after. Coba akses sebagai role non-Super Admin, verifikasi ditolak.

**Depends on**: Phase 2 (Foundational) — T002–T004 harus selesai lebih dulu.

### Tests for User Story 3

- [X] T020 [P] [US3] Feature test: perubahan pada `User` model tercatat di `activity_log` dengan causer & before/after, di `tests/Feature/ActivityLog/ActivityLogAccessTest.php`
- [X] T021 [P] [US3] Feature test: halaman activity log bisa diakses Super Admin dan mengembalikan 403/tersembunyi untuk role lain, di `tests/Feature/ActivityLog/ActivityLogAccessTest.php`

### Implementation for User Story 3

- [X] T022 [US3] Tambahkan trait `Spatie\Activitylog\Traits\LogsActivity` + `getActivitylogOptions()` pada `app/Models/User.php` (log perubahan atribut, sesuai [data-model.md](./data-model.md#activity-log-entry))
- [X] T023 [US3] Daftarkan halaman activity log dari `rmsramos/activitylog` di `app/Providers/Filament/AdminPanelProvider.php` (`->plugins([...])`)
- [X] T024 [US3] Batasi akses halaman activity log ke role Super Admin — implementasikan `canAccess()`/policy memakai Filament Shield yang sudah terinstall, konsisten dengan pola ACL `RoleResource` yang ada (FR-008)
- [X] T025 [US3] Tambahkan scheduled command `activitylog:clean` di `routes/console.php` (jadwal harian) untuk memenuhi retensi 90 hari dari T004 (FR-009)

**Checkpoint**: User Story 3 selesai — audit trail berjalan otomatis dan aman, independen dari US1/US2.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Verifikasi akhir lintas story sebelum dianggap selesai.

- [X] T026 [P] Jalankan `vendor/bin/pint --dirty --format agent` untuk merapikan seluruh file PHP yang diubah
- [X] T027 Jalankan seluruh test yang relevan: `php artisan test --compact --filter=GoogleAnalyticsWidgetTest`, `--filter=BrandSettingsTest`, `--filter=ActivityLogAccessTest`
- [X] T028 Jalankan `php artisan test --compact` penuh untuk memastikan tidak ada regresi ke test lain
- [ ] T029 Ikuti langkah verifikasi manual di [quickstart.md](./quickstart.md) untuk ketiga user story

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependency — mulai langsung
- **Foundational (Phase 2)**: Hanya memblokir US3 (Phase 5), TIDAK memblokir US1/US2
- **US1 (Phase 3)**: Bisa mulai setelah Phase 1, tidak menunggu Phase 2
- **US2 (Phase 4)**: Bisa mulai setelah Phase 1, tidak menunggu Phase 2, tidak bergantung pada US1
- **US3 (Phase 5)**: Butuh Phase 2 selesai; tidak bergantung pada US1/US2
- **Polish (Phase 6)**: Setelah story yang ingin dirilis selesai

### Parallel Opportunities

- T005 & T006 (test US1) paralel satu sama lain (file sama tapi test case berbeda — tetap ditulis berurutan dalam satu file, tandai [P] untuk penulisan konten, eksekusi tetap sequensial di file yang sama)
- T012 & T013 (test US2) sama seperti di atas
- T020 & T021 (test US3) sama seperti di atas
- **Antar story**: US1 (Phase 3), US2 (Phase 4), dan US3 (Phase 5, setelah Phase 2) bisa dikerjakan paralel oleh developer berbeda karena tidak saling bergantung
- T014 dan T016 [P] (US2) — file berbeda (`BrandSettings.php` vs media collection setup), bisa paralel

---

## Parallel Example: Mengerjakan ketiga story sekaligus (3 developer)

```bash
# Developer A — User Story 1 (setelah Phase 1)
Task: "T007–T011: config GA + custom Dashboard page + availability check"

# Developer B — User Story 2 (setelah Phase 1)
Task: "T014–T019: BrandSettings + media + Settings page + panel provider"

# Developer C — User Story 3 (setelah Phase 1 + Phase 2 selesai oleh salah satu dulu)
Task: "T022–T025: LogsActivity trait + plugin page + akses Super Admin + scheduler"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1: Setup
2. Phase 3: User Story 1 (GA dashboard widget)
3. **STOP & VALIDATE**: Test independen sesuai Independent Test di atas
4. Demo/rilis — nilai dari plugin GA yang sudah terinstall langsung terlihat

### Incremental Delivery

1. Phase 1 → Phase 3 (US1) → validasi → rilis (MVP)
2. Phase 4 (US2) → validasi → rilis
3. Phase 2 (Foundational) → Phase 5 (US3) → validasi → rilis
4. Phase 6: Polish setelah ketiganya rilis

### Catatan Constitution

- Setiap story WAJIB punya feature test sebelum ditandai selesai di tracker (Principle IV) — lihat T005/T006, T012/T013, T020/T021.
- Tidak ada task yang menambah page builder atau abstraksi baru di luar yang direncanakan (Principle III & V) — sesuai Constitution Check di [plan.md](./plan.md), tidak ada Complexity Tracking entry.

---

## Notes

- [P] = file berbeda / bagian independen, tidak saling bergantung
- [Story] memetakan task ke user story untuk traceability ke Linear (AMC-227 = US1, AMC-204 = US2, AMC-206 = US3)
- Commit setelah setiap task atau kelompok task logis
- Berhenti di checkpoint mana pun untuk validasi story secara independen
