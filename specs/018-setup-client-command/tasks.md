---

description: "Task list for Setup Client Command implementation"
---

# Tasks: Setup Client Command

**Input**: Design documents from `/specs/018-setup-client-command/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [contracts/cli-command-contract.md](./contracts/cli-command-contract.md), [quickstart.md](./quickstart.md)

**Tests**: Included — the constitution's Deployment & Client Setup Standards names this exact command, and it is the only reproducible path to provisioning a new client install, so it needs test coverage even though it isn't a "content module" under Principle IV.

**Organization**: Tasks are grouped by user story (US1, US2, US3 from spec.md) to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- File paths are relative to the repository root

## Path Conventions

Single Laravel monolith — Artisan command lives in `app/Console/Commands/`, auto-discovered per Laravel 13 convention (no manual Kernel registration needed).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold the command class

- [X] T001 Scaffold the command via `php artisan make:command SetupClientCommand --no-interaction` (creates `app/Console/Commands/SetupClientCommand.php`)
- [X] T002 Set the command `$signature` to `app:setup-client {name : Nama aplikasi untuk klien ini} {--force : Timpa .env dan APP_KEY yang sudah ada}` and `$description` in `app/Console/Commands/SetupClientCommand.php`, per [contracts/cli-command-contract.md](./contracts/cli-command-contract.md)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Nothing beyond the scaffolded signature is shared/blocking — this feature is a single command class, so there is no separate foundational layer. Proceed directly to User Story 1.

**Checkpoint**: Command class exists with its signature — user story implementation can begin.

---

## Phase 3: User Story 1 - Setup instalasi klien baru dalam satu langkah (Priority: P1) 🎯 MVP

**Goal**: Menjalankan `app:setup-client "Nama Klien"` pada repositori baru menghasilkan `.env` dari `.env.example`, berisi `APP_NAME` yang diberikan, dan `APP_KEY` yang valid.

**Independent Test**: Pada direktori kerja tanpa `.env`, jalankan command dengan sebuah nama; verifikasi `.env` tercipta, mengandung `APP_NAME` yang diberikan, dan `APP_KEY` terisi nilai valid.

### Tests for User Story 1

- [X] T003 [P] [US1] Feature test: fresh install (tanpa `.env`) menghasilkan `.env` berisi `APP_NAME` yang diberikan dan `APP_KEY` terisi, exit code `0`, in `tests/Feature/Console/SetupClientCommandTest.php`
- [X] T004 [P] [US1] Feature test: argumen `name` kosong/hanya spasi membuat command gagal (exit code bukan `0`) tanpa membuat/mengubah `.env`, in `tests/Feature/Console/SetupClientCommandTest.php`
- [X] T005 [P] [US1] Feature test: `.env.example` tidak ditemukan (dan `.env` belum ada) membuat command berhenti dengan pesan error yang jelas, exit code bukan `0`, in `tests/Feature/Console/SetupClientCommandTest.php`

### Implementation for User Story 1

- [X] T006 [US1] Implement validasi argumen `name` (tolak kosong/hanya spasi dengan `$this->fail(...)` atau `$this->error()` + return non-zero) di awal `handle()` dalam `app/Console/Commands/SetupClientCommand.php`
- [X] T007 [US1] Implement pembuatan `.env` dari `.env.example` via `Illuminate\Filesystem\Filesystem` ketika `.env` belum ada, termasuk penanganan `.env.example` tidak ditemukan (error jelas, exit non-zero) di `app/Console/Commands/SetupClientCommand.php`
- [X] T008 [US1] Implement penulisan `APP_NAME` ke `.env` (regex replace baris `APP_NAME=`, tambahkan bila belum ada, quote nilai yang mengandung spasi/karakter khusus per research.md #2) di `app/Console/Commands/SetupClientCommand.php`
- [X] T009 [US1] ~~Implement pemanggilan `key:generate` via `$this->call(...)`~~ — **direvisi saat implementasi**: generate key langsung memakai `Encrypter::generateKey()` + tulis lewat helper `.env` sendiri, hanya ketika `APP_KEY` belum terisi di file (bukan `config('app.key')` yang bisa stale) — lihat research.md #3 untuk alasan lengkap. Diimplementasikan di `app/Console/Commands/SetupClientCommand.php`

**Checkpoint**: MVP complete — `php artisan app:setup-client "Nama"` pada repo baru menghasilkan instalasi siap pakai.

---

## Phase 4: User Story 2 - Cache bersih pasca-setup (Priority: P1)

**Goal**: Setiap eksekusi command membersihkan cache config, route, view, dan application — tanpa syarat, terlepas dari apakah langkah `.env`/key dijalankan atau dilewati.

**Independent Test**: Pada instalasi dengan cache config/route/view tersimpan, jalankan command dan verifikasi keempat jenis cache tersebut kosong/diperbarui setelahnya.

### Tests for User Story 2

- [X] T010 [P] [US2] Feature test: setelah command dijalankan, `config:clear`/`route:clear`/`view:clear`/`cache:clear` telah dieksekusi (assert via `Artisan::shouldReceive`/Facade spy atau efek cache file terhapus) in `tests/Feature/Console/SetupClientCommandTest.php`
- [X] T011 [P] [US2] Feature test: pada re-run dengan `.env`/`APP_KEY` sudah ada (langkah tsb dilewati), cache tetap dibersihkan in `tests/Feature/Console/SetupClientCommandTest.php`

### Implementation for User Story 2

- [X] T012 [US2] Implement pemanggilan `config:clear`, `route:clear`, `view:clear`, `cache:clear` via `$this->call(...)` di akhir `handle()`, dijalankan tanpa syarat (research.md #4) di `app/Console/Commands/SetupClientCommand.php`

**Checkpoint**: Cache selalu bersih pasca-eksekusi, baik pada fresh install maupun re-run.

---

## Phase 5: User Story 3 - Perlindungan dari penimpaan konfigurasi tanpa sengaja (Priority: P2)

**Goal**: Menjalankan ulang command pada instalasi yang sudah dikonfigurasi tidak mengubah `.env`/`APP_KEY` yang sudah ada kecuali `--force` diberikan.

**Independent Test**: Pada instalasi dengan `.env`/`APP_KEY` sudah ada, jalankan command tanpa `--force` lalu bandingkan isi `.env`/`APP_KEY` sebelum-sesudah (harus identik); ulangi dengan `--force` dan verifikasi keduanya berubah.

### Tests for User Story 3

- [X] T013 [P] [US3] Feature test: `.env`/`APP_KEY` sudah ada, command dijalankan tanpa `--force` → `.env` dan `APP_KEY` tidak berubah sama sekali, pesan "dilewati" ditampilkan, exit code `0` in `tests/Feature/Console/SetupClientCommandTest.php`
- [X] T014 [P] [US3] Feature test: `.env`/`APP_KEY` sudah ada, command dijalankan dengan `--force` → `.env` ditimpa ulang dari template dengan `APP_NAME` baru, dan `APP_KEY` berganti nilai in `tests/Feature/Console/SetupClientCommandTest.php`

### Implementation for User Story 3

- [X] T015 [US3] Tambahkan pengecekan "`.env` sudah ada" sebelum langkah copy+set nama (T007/T008): lewati seluruhnya dengan pesan info kecuali `--force`, per tabel perilaku di [contracts/cli-command-contract.md](./contracts/cli-command-contract.md) di `app/Console/Commands/SetupClientCommand.php`
- [X] T016 [US3] Tambahkan pengecekan "`APP_KEY` sudah terisi" sebelum memanggil `key:generate` (T009): lewati dengan pesan info kecuali `--force` (teruskan `--force` ke `key:generate`) di `app/Console/Commands/SetupClientCommand.php`

**Checkpoint**: Re-run command pada instalasi yang sudah dikonfigurasi aman secara default; `--force` tersedia untuk penimpaan yang disengaja.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Ringkasan output, kebersihan kode, dan validasi akhir

- [X] T017 [P] Tambahkan ringkasan hasil akhir yang jelas (langkah dibuat/dilewati/gagal) di akhir `handle()` per FR-008, di `app/Console/Commands/SetupClientCommand.php`
- [X] T018 Run `vendor/bin/pint --dirty --format agent` dan perbaiki pelanggaran gaya
- [X] T019 Run `php artisan test --compact` untuk seluruh suite dan perbaiki regresi
- [X] T020 Jalankan langkah verifikasi manual di [quickstart.md](./quickstart.md) end-to-end

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependensi — mulai langsung
- **Foundational (Phase 2)**: Kosong untuk fitur ini (lihat catatan di atas)
- **User Story 1 (Phase 3)**: Bergantung pada Setup — tidak bergantung pada US2/US3
- **User Story 2 (Phase 4)**: Bergantung pada Setup; secara independen dapat diuji begitu `handle()` dasar ada, meski secara implementasi menambah kode di akhir method yang sama dengan US1
- **User Story 3 (Phase 5)**: Bergantung pada implementasi US1 (T007/T008/T009) karena menambahkan syarat sebelum langkah-langkah tersebut dieksekusi
- **Polish (Phase 6)**: Bergantung pada seluruh user story selesai

### Within Each User Story

- Tests ditulis sebelum implementasi, dan harus gagal sebelum task implementasi terkait selesai
- US1 harus selesai lebih dulu karena US3 memodifikasi kondisi guard di sekitar langkah yang dibangun US1

### Parallel Opportunities

- T003–T005 (test US1) dapat paralel
- T010–T011 (test US2) dapat paralel
- T013–T014 (test US3) dapat paralel
- T017–T018 (polish, file berbeda secara efek) dapat dikerjakan berdekatan meski menyentuh file yang sama secara berurutan

---

## Parallel Example: User Story 1 Tests

```bash
Task: "Feature test fresh install menghasilkan .env+APP_KEY in tests/Feature/Console/SetupClientCommandTest.php"
Task: "Feature test nama kosong gagal in tests/Feature/Console/SetupClientCommandTest.php"
Task: "Feature test .env.example hilang gagal in tests/Feature/Console/SetupClientCommandTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 3: User Story 1
3. **STOP and VALIDATE**: `php artisan app:setup-client "Nama"` pada repo baru menghasilkan `.env`+`APP_KEY` siap pakai
4. Demo/pakai untuk provisioning klien pertama jika mendesak

### Incremental Delivery

1. Setup → command class + signature siap
2. Tambah User Story 1 → fresh install bekerja end-to-end → demo (MVP)
3. Tambah User Story 2 → cache selalu bersih → demo
4. Tambah User Story 3 → aman di-re-run, `--force` tersedia → demo
5. Polish → ringkasan output, pint, full test suite, quickstart validation

---

## Notes

- [P] tasks touch different test methods in the same file or are otherwise independent — verify no conflicting edits before running truly in parallel
- Constitution's Deployment & Client Setup Standards names `app:setup-client` explicitly — this command IS the required reproducibility mechanism, so T003–T005, T010–T011, T013–T014 are not optional
- Constitution Principle V: no new Composer dependency — the command only orchestrates Laravel's own `key:generate`, `config:clear`, `route:clear`, `view:clear`, `cache:clear`
- Commit after each task or logical group; run `vendor/bin/pint --dirty --format agent` before finalizing any PHP change
