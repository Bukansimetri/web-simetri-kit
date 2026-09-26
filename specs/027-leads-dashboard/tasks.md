---

description: "Task list for 027-leads-dashboard"
---

# Tasks: Dashboard Prospek Admin

**Input**: Design documents from `/specs/027-leads-dashboard/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/dashboard-ui.md, quickstart.md

**Tests**: Diwajibkan oleh constitution Principle IV dan plan.md (Project Structure → `tests/Feature/Dashboard/`). Test ditulis sebelum implementasi di tiap story dan harus gagal dulu.

**Organization**: Tasks dikelompokkan per user story; tiap story menambah metode ke service bersama dan satu widget, sehingga bisa diuji sendiri.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa paralel (file berbeda, tanpa ketergantungan pada task yang belum selesai)
- **[Story]**: US1, US2, US3

## Aturan umum untuk semua task

- Semua label UI berbahasa Indonesia, sesuai `contracts/dashboard-ui.md`.
- Semua batas waktu memakai zona waktu `app(\App\Settings\SiteSettings::class)->timezone` (research.md §2), bukan `config('app.timezone')`.
- Test memakai `RefreshDatabase`, factory, dan `$this->travelTo(...)` untuk mengunci "sekarang". Untuk mengakses panel, buat role (misal `Role::create(['name' => 'super_admin'])`) dan `assignRole` karena `User::canAccessPanel()` mewajibkan user punya role (lihat `tests/Feature/Admin/NavigationStructureTest.php`).
- Widget di-test dengan `Livewire::test(WidgetClass::class)` (pola di `tests/Feature/Settings/AboutPageSettingsTest.php`).
- Setelah menyunting PHP: `vendor/bin/pint --dirty --format agent`.

---

## Phase 1: Setup

- [X] T001 [P] Buat `database/factories/CalculatorLeadFactory.php` dengan `php artisan make:factory CalculatorLeadFactory --model=CalculatorLead --no-interaction`. Isi `definition()` untuk semua kolom wajib di `database/migrations/2026_09_14_100000_create_calculator_leads_table.php`: `name`, `phone` (`'08'.fake()->numerify('##########')`), `area` (`fake()->city()`), `category` (`residential`/`industrial`), `method` = `CalculatorLead::METHOD_BILL`, `monthly_bill`, `estimated_monthly_bill` (misal `fake()->numberBetween(300_000, 5_000_000)`), `savings_year1`, `total_savings_25y`, `estimated_investment`, `breakeven_years`, `annual_kwh`, `assumptions` (`[]`), `status` = `CalculatorLead::STATUS_NEW`. Tambahkan state `won()` yang mengeset `status` ke `CalculatorLead::STATUS_WON`. Ikuti gaya `database/factories/ContactSubmissionFactory.php`
- [X] T002 [P] Hapus `Widgets\AccountWidget::class` dan `Widgets\FilamentInfoWidget::class` dari `->widgets([...])` di `app/Providers/Filament/AdminPanelProvider.php` (sisakan `->widgets([])` atau hapus pemanggilannya, lalu hapus `use Filament\Widgets;` bila tak terpakai). Jangan ubah `discoverWidgets(...)` karena widget baru akan ditemukan dari `app/Filament/Widgets/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Kerangka service dengan aturan zona waktu yang dipakai ketiga story.

- [X] T003 Tulis test `tests/Feature/Dashboard/LeadDashboardMetricsTest.php` (buat dengan `php artisan make:test --phpunit Dashboard/LeadDashboardMetricsTest --no-interaction`) untuk helper zona waktu: dengan `SiteSettings::timezone = 'Asia/Jakarta'` dan `travelTo('2026-09-10 18:30:00 UTC')` (= 11 Sep 01.30 WIB), `startOfLocalDaysAgo(0)` bernilai `2026-09-10 17:00:00 UTC` (awal 11 Sep WIB); ubah timezone ke `UTC` dan pastikan hasilnya `2026-09-10 00:00:00 UTC`
- [X] T004 Buat `app/Services/LeadDashboardMetrics.php` (`php artisan make:class Services/LeadDashboardMetrics --no-interaction`) dengan: `timezone(): string` (dari `SiteSettings`), `now(): CarbonImmutable` (sekarang di zona situs), dan `startOfLocalDaysAgo(int $days): CarbonImmutable` (awal hari lokal `$days` hari lalu, dikembalikan dalam UTC untuk dipakai di query). PHPDoc singkat mengacu research.md §2. Jalankan test T003 sampai lulus

**Checkpoint**: Service ada, aturan zona waktu teruji.

---

## Phase 3: User Story 1 - Ringkasan prospek sekilas (Priority: P1) 🎯 MVP

**Goal**: Empat kartu statistik di dashboard, dua di antaranya membuka daftar terfilter (FR-010–FR-015).

**Independent Test**: `php artisan test --compact tests/Feature/Dashboard/LeadStatsOverviewTest.php --filter=LeadDashboardMetricsTest` lalu cek manual quickstart.md §3 langkah 1–3.

### Tests for User Story 1

- [X] T005 [US1] Tambah test di `tests/Feature/Dashboard/LeadDashboardMetricsTest.php`:
  - `newContactCount()` / `newCalculatorCount()` hanya menghitung status `new` (siapkan 3 new + 2 contacted pesan, 4 new + 1 won lead).
  - `last30Days()` mengembalikan `total` gabungan dan `daily` berisi tepat 30 angka; prospek 31 hari lalu tidak dihitung; prospek pukul 01.00 WIB masuk ke tanggal lokal yang benar; hari tanpa prospek bernilai 0.
  - `conversion()`: 1 won dari 10 lead dalam 90 hari → `['won' => 1, 'total' => 10, 'rate' => 10.0]`; lead 91 hari lalu tidak dihitung; tanpa lead → `rate` `null` (tanpa pembagian nol).
- [X] T006 [P] [US1] Buat `tests/Feature/Dashboard/LeadStatsOverviewTest.php`: render widget dengan `Livewire::test(LeadStatsOverview::class)` sebagai user ber-role; assert tampil label "Pesan masuk baru", "Lead kalkulator baru", "Prospek 30 hari", "Konversi lead kalkulator", nilai yang benar, "Belum ada data" saat tanpa lead, dan dua URL daftar terfilter (`ContactSubmissionResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'new']]])` dan versi `CalculatorLeadResource`). Tambah satu test yang membuka URL pesan masuk terfilter dan memastikan hanya pesan berstatus baru yang tampil

### Implementation for User Story 1

- [X] T007 [US1] Tambah ke `app/Services/LeadDashboardMetrics.php`: `newContactCount(): int`, `newCalculatorCount(): int`, `last30Days(): array{total: int, daily: array<int, int>}` (ambil hanya `created_at` kedua tabel sejak `startOfLocalDaysAgo(29)`, kelompokkan per tanggal lokal di PHP, isi 0 untuk hari kosong), `conversion(): array{won: int, total: int, rate: ?float}` (lead sejak `startOfLocalDaysAgo(89)`, `round(won / total * 100, 1)`). Pakai konstanta status dari model, bukan string. Jalankan test T005 sampai lulus
- [X] T008 [US1] Buat `app/Filament/Widgets/LeadStatsOverview.php` (`php artisan make:filament-widget LeadStatsOverview --stats-overview --panel=admin --no-interaction` atau manual) extends `StatsOverviewWidget`: `protected static ?int $sort = 1`; `canView()` = `ContactSubmissionResource::canViewAny() && CalculatorLeadResource::canViewAny()`; `getStats()` mengembalikan empat `Stat` sesuai tabel kartu di `contracts/dashboard-ui.md` (deskripsi, `->url()` untuk kartu 1–2, `->chart($daily)` untuk kartu 3, warna `danger` bila kartu 1–2 > 0, format persen dengan koma desimal, misal `10,0%`). Tidak ada query di `getHeading()` / heading. Jalankan test T006 sampai lulus

**Checkpoint**: Dashboard sudah berguna dengan empat kartu. MVP bisa di-review.

---

## Phase 4: User Story 2 - Daftar prospek perlu ditindaklanjuti (Priority: P2)

**Goal**: Tabel gabungan 10 prospek baru terbaru dengan penanda terlambat (FR-020–FR-026).

**Independent Test**: `php artisan test --compact tests/Feature/Dashboard/LeadsNeedingFollowUpTest.php` lalu cek manual quickstart.md §3 langkah 4–5.

### Tests for User Story 2

- [X] T009 [US2] Tambah test `followUpQueue()` di `tests/Feature/Dashboard/LeadDashboardMetricsTest.php`: gabungan kedua sumber berstatus new saja, urut terbaru, maks. 10 walau ada 15; `isOverdue` true untuk prospek 49 jam dan false untuk 47 jam; `estimatedMonthlyBill` terisi untuk lead kalkulator dan `null` untuk pesan masuk; `url` menunjuk halaman edit resource yang benar
- [X] T010 [P] [US2] Buat `tests/Feature/Dashboard/LeadsNeedingFollowUpTest.php`: render widget; assert judul "Perlu ditindaklanjuti", kolom Nama/Sumber/Area/Masuk/Estimasi Tagihan, badge "Terlambat" pada prospek > 48 jam, "Rp 1.250.000" untuk lead dengan estimasi 1250000, "-" untuk area kosong, prospek non-new tidak tampil, teks "Semua prospek sudah ditindaklanjuti" saat kosong, dan dua tautan "Lihat semua ..." di footer

### Implementation for User Story 2

- [X] T011 [US2] Tambah `followUpQueue(int $limit = 10): array` ke `app/Services/LeadDashboardMetrics.php`: ambil maks. `$limit` terbaru berstatus new dari tiap model (kolom `id`, `name`, `area`, `created_at`, dan `estimated_monthly_bill` untuk lead), petakan ke array dengan field di data-model.md §"Item antrean tindak lanjut" (`ageLabel` via `diffForHumans()` dengan locale `id`; `isOverdue` bila > 48 jam; `url` via `ContactSubmissionResource::getUrl('edit', ['record' => $id])` / `CalculatorLeadResource::getUrl('edit', ...)`), gabungkan, urutkan `createdAt` menurun, potong `$limit`. Jalankan test T009 sampai lulus
- [X] T012 [US2] Buat `app/Filament/Widgets/LeadsNeedingFollowUp.php` extends `Filament\Widgets\Widget`: `protected static string $view = 'filament.widgets.leads-needing-follow-up'`, `protected static ?int $sort = 2`, `protected int | string | array $columnSpan = 'full'`, `canView()` sama seperti T008, dan method publik yang mengirim `followUpQueue()` serta dua URL daftar terfilter ke view
- [X] T013 [US2] Buat `resources/views/filament/widgets/leads-needing-follow-up.blade.php`: `<x-filament-widgets::widget>` + `<x-filament::section heading="Perlu ditindaklanjuti">`, tabel HTML dengan kelas Tailwind Filament (lihat view tabel Filament untuk gaya), badge sumber dan "Terlambat" dengan `<x-filament::badge>`, baris dapat diklik (tautan ke `url`), pesan kosong, dan footer dua tautan `<x-filament::link>`. Semua teks sesuai `contracts/dashboard-ui.md`. Jalankan test T010 sampai lulus

**Checkpoint**: Kartu + tabel berfungsi, masing-masing teruji.

---

## Phase 5: User Story 3 - Tren prospek mingguan (Priority: P3)

**Goal**: Grafik bar 12 minggu, dua seri (FR-030–FR-033).

**Independent Test**: `php artisan test --compact tests/Feature/Dashboard/WeeklyLeadsChartTest.php` lalu cek manual quickstart.md §3 langkah 6–7.

### Tests for User Story 3

- [X] T014 [US3] Tambah test `weeklySeries()` di `tests/Feature/Dashboard/LeadDashboardMetricsTest.php`: tepat 12 minggu berakhir di minggu berjalan, minggu dimulai Senin di zona situs (prospek Minggu 23.30 WIB masuk minggu sebelumnya, Senin 00.30 WIB masuk minggu berikutnya), minggu kosong bernilai 0, prospek > 12 minggu tidak dihitung, semua status dihitung, dua seri terpisah
- [X] T015 [P] [US3] Buat `tests/Feature/Dashboard/WeeklyLeadsChartTest.php`: render widget; assert heading "Prospek per minggu", dua dataset berlabel "Pesan Masuk" dan "Lead Kalkulator", 12 label dengan format `j M` (misal `7 Sep`)

### Implementation for User Story 3

- [X] T016 [US3] Tambah `weeklySeries(int $weeks = 12): array{labels: array<int, string>, contact: array<int, int>, calculator: array<int, int>}` ke `app/Services/LeadDashboardMetrics.php`: batas bawah = Senin awal minggu ke-`$weeks` di zona situs (dikonversi ke UTC), ambil `created_at` kedua tabel, kelompokkan per Senin lokal, isi 0 untuk minggu kosong, label `translatedFormat('j M')`. Jalankan test T014 sampai lulus
- [X] T017 [US3] Buat `app/Filament/Widgets/WeeklyLeadsChart.php` (`php artisan make:filament-widget WeeklyLeadsChart --chart --panel=admin --no-interaction` atau manual) extends `ChartWidget`: `protected static ?string $heading = 'Prospek per minggu'`, `protected static ?string $description = '12 minggu terakhir'` (atau `getDescription()`), `protected static ?int $sort = 3`, `columnSpan = 'full'`, `getType()` = `'bar'`, `getData()` dari `weeklySeries()` dengan dua dataset, `canView()` sama seperti T008. Heading tanpa query. Jalankan test T015 sampai lulus

**Checkpoint**: Ketiga widget selesai dan teruji.

---

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T018 Buat `tests/Feature/Dashboard/DashboardPageTest.php`: (a) GET `/admin` sebagai user ber-role → 200, tidak ada teks khas `AccountWidget`/`FilamentInfoWidget` (misal link "filamentphp.com" dan tombol "Keluar" di dalam widget akun), tampil ketiga judul widget; (b) instalasi tanpa data → 200 dan tampil "Belum ada data" serta "Semua prospek sudah ditindaklanjuti"; (c) FR-003: daftarkan policy penolak sementara di test (`Gate::policy(ContactSubmission::class, ...)` dengan `viewAny` false) → GET `/admin` 200 dan tidak tampil ketiga judul widget
- [X] T019 Jalankan `vendor/bin/pint --dirty --format agent`, lalu `php artisan test --compact tests/Feature/Dashboard tests/Feature/Admin/RoleResourceEditTest.php tests/Feature/Admin/NavigationStructureTest.php`; perbaiki sampai lulus (RoleResourceEditTest memastikan heading widget tidak memicu error di halaman edit Role, research.md §7)
- [X] T020 [P] Perbarui `docs/arsitektur.md`: tambah baris `app/Filament/Widgets/` di tabel "Peta direktori" dan `app/Services/LeadDashboardMetrics.php` sebagai contoh `app/Services/`; perbarui tanggal "Terakhir diperbarui". Jalankan `php artisan test --compact tests/Feature/Docs`
- [X] T021 Verifikasi manual sesuai `specs/027-leads-dashboard/quickstart.md` §2–3 di lokal (data contoh lewat factory), lalu tanyakan ke user apakah ingin menjalankan seluruh test suite

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: T001 dan T002 independen, bisa paralel.
- **Foundational (Phase 2)**: T003 → T004. Butuh T001 untuk test berikutnya, tidak untuk T003/T004.
- **US1, US2, US3**: masing-masing bergantung pada Phase 1–2. Ketiganya menambah metode ke `app/Services/LeadDashboardMetrics.php` dan test ke `tests/Feature/Dashboard/LeadDashboardMetricsTest.php`, jadi kerjakan berurutan P1 → P2 → P3 untuk menghindari konflik suntingan file yang sama. Secara logika tidak ada story yang bergantung pada story lain.
- **Polish (Phase 6)**: setelah story yang ingin dirilis selesai; T018 mengasumsikan ketiga widget ada.

### Within Each User Story

- Test service (T005/T009/T014) → implementasi service (T007/T011/T016) → widget (T008/T012–T013/T017).
- Test widget (T006/T010/T015) boleh ditulis paralel dengan test service karena beda file.

### Parallel Opportunities

- T001 ∥ T002.
- T006 ∥ T005, T010 ∥ T009, T015 ∥ T014 (file test berbeda).
- T020 ∥ T019.

## Parallel Example: User Story 1

```text
T005 Test metrik kartu di tests/Feature/Dashboard/LeadDashboardMetricsTest.php
T006 Test widget kartu di tests/Feature/Dashboard/LeadStatsOverviewTest.php
```

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1 (factory, hapus widget bawaan) + Phase 2 (service & zona waktu).
2. Phase 3 (kartu statistik).
3. **Stop & validasi**: test US1 lulus, cek quickstart §3 langkah 1–3.

### Incremental Delivery

1. MVP kartu → review.
2. Tabel "Perlu ditindaklanjuti" → test + cek manual.
3. Grafik mingguan → test + cek manual.
4. Polish: test halaman dashboard & akses, regresi halaman Role, dokumentasi arsitektur.
