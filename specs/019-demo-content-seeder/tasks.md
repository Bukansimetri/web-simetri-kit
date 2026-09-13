---

description: "Task list for Demo Content Seeder implementation"
---

# Tasks: Demo Content Seeder

**Input**: Design documents from `/specs/019-demo-content-seeder/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/cli-command-contract.md](./contracts/cli-command-contract.md), [quickstart.md](./quickstart.md)

**Tests**: Included — this feature both adds new tooling and fixes an existing deployment-standard violation (demo content auto-seeding), so regression coverage matters even though `demo:seed`/`demo:clean` aren't "content modules" under Principle IV.

**Organization**: Tasks are grouped by user story (US1, US2, US3 from spec.md) to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- File paths are relative to the repository root

## Path Conventions

Single Laravel monolith — Artisan commands in `app/Console/Commands/`, seeders in `database/seeders/`, one new lightweight model in `app/Models/`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Schema and model for the demo-seed manifest that every user story depends on

- [X] T001 Create migration for `demo_seed_records` (columns: `id`, `seedable_type`, `seedable_id`, `created_at`; unique index on `(seedable_type, seedable_id)`) via `php artisan make:migration create_demo_seed_records_table --no-interaction`, then implement per [data-model.md](./data-model.md) in the generated file under `database/migrations/`
- [X] T002 [P] Create `DemoSeedRecord` model (`morphTo` as `seedable`, no `updated_at`) via `php artisan make:model DemoSeedRecord --no-interaction`, implemented in `app/Models/DemoSeedRecord.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Manifest read/write helper shared by both seeding (US1) and cleaning (US3)

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T003 Add static helper methods to `DemoSeedRecord` in `app/Models/DemoSeedRecord.php`: `recordFor(Model $model): void` (writes one manifest row for a just-created record) and `alreadySeeded(string $modelClass): bool` (true if any manifest row exists for that model class) — used by every demo seeder to stay idempotent (research.md #4)

**Checkpoint**: Manifest read/write primitives ready — user story implementation can begin.

---

## Phase 3: User Story 1 - Mengisi instalasi demo dengan konten contoh yang realistis (Priority: P1) 🎯 MVP

**Goal**: Menjalankan `php artisan demo:seed` pada database kosong mengisi Layanan, Tim, Testimoni, dan Portfolio dengan konten contoh realistis, tercatat di manifest, dan aman dijalankan berulang.

**Independent Test**: Pada database kosong, jalankan `demo:seed`; verifikasi kelima entitas (kategori portfolio, layanan, tim, testimoni, proyek portfolio) terisi dan halaman publik terkait menampilkannya.

### Tests for User Story 1

- [X] T004 [P] [US1] Feature test: `demo:seed` pada database kosong mengisi kelima entitas dan mencatat baris manifest untuk masing-masing, exit code `0`, in `tests/Feature/Console/DemoSeedCommandTest.php`
- [X] T005 [P] [US1] Feature test: menjalankan `demo:seed` dua kali berturut-turut tidak menghasilkan duplikasi baris pada entitas manapun, in `tests/Feature/Console/DemoSeedCommandTest.php`

### Implementation for User Story 1

- [X] T006 [US1] Buat `PortfolioDemoSeeder` (beberapa `PortfolioCategory` + `PortfolioProject` contoh yang realistis, mereferensikan kategori yang baru dibuat) yang memanggil `DemoSeedRecord::recordFor(...)` untuk tiap baris dan dilewati bila `DemoSeedRecord::alreadySeeded(...)` true, di `database/seeders/PortfolioDemoSeeder.php`
- [X] T007 [US1] Buat `TeamMemberSeeder` (beberapa anggota tim contoh: nama, jabatan, bio) dengan pola manifest+guard yang sama, di `database/seeders/TeamMemberSeeder.php`
- [X] T008 [US1] Modifikasi `ProductSeeder` agar tiap produk yang dibuat dicatat lewat `DemoSeedRecord::recordFor(...)`, dan seluruh seeder dilewati bila `DemoSeedRecord::alreadySeeded(Product::class)` true, di `database/seeders/ProductSeeder.php`
- [X] T009 [US1] Modifikasi `TestimonialSeeder` dengan pola manifest+guard yang sama, di `database/seeders/TestimonialSeeder.php`
- [X] T010 [US1] Buat `DemoContentSeeder` yang memanggil kelima seeder di atas dalam urutan pada [data-model.md](./data-model.md) §Urutan operasi (kategori portfolio & produk dulu, baru proyek portfolio yang mereferensikan kategori), di `database/seeders/DemoContentSeeder.php`
- [X] T011 [US1] Buat command `demo:seed` (via `php artisan make:command DemoSeedCommand --no-interaction`) yang memanggil `DemoContentSeeder` dan menampilkan ringkasan (entitas diisi vs dilewati) per FR-008-setara di [contracts/cli-command-contract.md](./contracts/cli-command-contract.md), di `app/Console/Commands/DemoSeedCommand.php`

**Checkpoint**: MVP complete — `php artisan demo:seed` mengisi instalasi demo end-to-end, aman diulang.

---

## Phase 4: User Story 2 - Konten demo tidak pernah tercampur ke instalasi produksi klien (Priority: P1)

**Goal**: Proses setup instalasi standar (`db:seed`/`migrate --seed`, `app:setup-client`) tidak pernah menghasilkan konten demo di keempat modul.

**Independent Test**: Jalankan `php artisan migrate --seed` pada database kosong tanpa memanggil `demo:seed`; verifikasi Layanan, Tim, Testimoni, Portfolio kosong.

### Tests for User Story 2

- [X] T012 [P] [US2] Feature test: `php artisan db:seed` (memanggil `DatabaseSeeder` tanpa `--class`) TIDAK menghasilkan satu pun baris `Product`, `Testimonial`, `TeamMember`, atau `PortfolioProject`, in `tests/Feature/Console/DatabaseSeederDemoIsolationTest.php`

### Implementation for User Story 2

- [X] T013 [US2] Hapus pemanggilan `ProductSeeder::class` dan `TestimonialSeeder::class` dari `DatabaseSeeder::run()` di `database/seeders/DatabaseSeeder.php` (biarkan `CategorySeeder` tetap ada — taxonomy struktural, bukan konten demo, lihat research.md #3)

**Checkpoint**: Setup instalasi standar terbukti bersih dari konten demo di keempat modul.

---

## Phase 5: User Story 3 - Membersihkan konten demo sebelum go-live (Priority: P2)

**Goal**: `php artisan demo:clean` menghapus seluruh konten demo tanpa menyentuh data yang ditambahkan admin secara terpisah, dan menjaga konsistensi relasi kategori↔proyek portfolio.

**Independent Test**: Pada instalasi berisi konten demo plus satu entri manual admin, jalankan `demo:clean`; verifikasi konten demo hilang dan entri manual tetap ada.

### Tests for User Story 3

- [X] T014 [P] [US3] Feature test: setelah `demo:seed`, menjalankan `demo:clean` menghapus seluruh baris kelima entitas beserta manifest-nya, exit code `0`, in `tests/Feature/Console/DemoCleanCommandTest.php`
- [X] T015 [P] [US3] Feature test: entri yang dibuat admin secara manual (bukan lewat `demo:seed`) TIDAK ikut terhapus oleh `demo:clean`, in `tests/Feature/Console/DemoCleanCommandTest.php`
- [X] T016 [P] [US3] Feature test: `demo:clean` pada manifest kosong (belum pernah `demo:seed`) selesai tanpa error dan tanpa perubahan data, exit code `0`, in `tests/Feature/Console/DemoCleanCommandTest.php`
- [X] T017 [P] [US3] Feature test: `PortfolioCategory` demo TIDAK dihapus oleh `demo:clean` bila masih direferensikan oleh `PortfolioProject` lain (dibuat manual, non-demo) setelah proyek-proyek demo dihapus, in `tests/Feature/Console/DemoCleanCommandTest.php`

### Implementation for User Story 3

- [X] T018 [US3] Buat command `demo:clean` (via `php artisan make:command DemoCleanCommand --no-interaction`) yang menghapus baris manifest dalam urutan kebalikan pada [data-model.md](./data-model.md) §Urutan operasi (proyek portfolio → testimoni → tim → produk → kategori portfolio bila tidak dipakai lagi), lalu menghapus baris `demo_seed_records` yang diproses, di `app/Console/Commands/DemoCleanCommand.php`

**Checkpoint**: Siklus hidup konten demo lengkap — isi, tunjukkan, bersihkan — tanpa risiko menghapus data asli klien.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Kebersihan kode dan validasi akhir

- [X] T019 Run `vendor/bin/pint --dirty --format agent` dan perbaiki pelanggaran gaya
- [X] T020 Run `php artisan test --compact` untuk seluruh suite dan perbaiki regresi
- [X] T021 Jalankan langkah verifikasi manual di [quickstart.md](./quickstart.md) end-to-end

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependensi — mulai langsung
- **Foundational (Phase 2)**: Bergantung pada Setup — BLOCKS seluruh user story
- **User Story 1 (Phase 3)**: Bergantung pada Foundational — tidak bergantung pada US2/US3
- **User Story 2 (Phase 4)**: Bergantung pada Foundational saja; independen dari US1 secara nilai bisnis, tapi secara implementasi lebih mudah diverifikasi setelah US1 ada (untuk memastikan seeder yang "seharusnya tidak otomatis jalan" itu benar-benar sudah ada)
- **User Story 3 (Phase 5)**: Bergantung pada US1 (butuh data & manifest hasil `demo:seed` untuk dibersihkan)
- **Polish (Phase 6)**: Bergantung pada seluruh user story selesai

### Parallel Opportunities

- T001–T002 (Setup, file berbeda) dapat paralel
- T004–T005 (test US1) dapat paralel
- T012 (test US2, satu-satunya) berjalan sendiri
- T014–T017 (test US3) dapat paralel

---

## Parallel Example: User Story 1 Tests

```bash
Task: "Feature test demo:seed mengisi kelima entitas in tests/Feature/Console/DemoSeedCommandTest.php"
Task: "Feature test demo:seed idempoten in tests/Feature/Console/DemoSeedCommandTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: `php artisan demo:seed` mengisi keempat modul, aman diulang
5. Demo ke calon klien pertama jika mendesak (meski US2 belum selesai — risiko kebocoran ke produksi baru relevan saat instalasi klien sungguhan disiapkan)

### Incremental Delivery

1. Setup + Foundational → manifest siap
2. Tambah User Story 1 → demo bisa diisi → demo (MVP)
3. Tambah User Story 2 → terbukti tidak bocor ke setup produksi → aman dipakai lintas instalasi klien
4. Tambah User Story 3 → siklus hidup lengkap (bisa dibersihkan sebelum go-live)
5. Polish → pint, full test suite, quickstart validation

---

## Notes

- [P] tasks touch different files or independent test methods
- Constitution's Deployment & Client Setup Standards explicitly forbids demo content auto-running in production — T013 (US2) directly remediates an existing violation in `DatabaseSeeder`
- Constitution Principle V: no new Composer dependency — the manifest is a single small table + model using Eloquent's own morph relations
- Commit after each task or logical group; run `vendor/bin/pint --dirty --format agent` before finalizing any PHP change
