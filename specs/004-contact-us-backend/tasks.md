---

description: "Task list for Contact Us Backend"
---

# Tasks: Contact Us Backend

**Input**: Design documents from `/specs/004-contact-us-backend/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/submit-endpoint.md](./contracts/submit-endpoint.md), [contracts/admin-panel-surface.md](./contracts/admin-panel-surface.md), [quickstart.md](./quickstart.md)

**Tests**: Disertakan — Constitution Principle IV (Module Test Coverage) mewajibkan feature test dasar sebelum sebuah unit kerja dianggap selesai.

**Organization**: Tasks dikelompokkan per user story (US1–US3, sesuai prioritas P1/P2/P3 di spec.md) supaya masing-masing bisa dikerjakan, ditest, dan dirilis independen.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak saling bergantung)
- **[Story]**: User story terkait (US1–US3)
- Path file selalu eksplisit

## Path Conventions

Perluasan langsung controller/view publik dan admin panel Filament yang sudah ada — semua path relatif ke root repo (lihat [plan.md](./plan.md) § Project Structure).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verifikasi baseline sebelum perubahan dimulai.

- [X] T001 Jalankan `vendor/bin/pint --format agent` dan `php artisan test --compact` untuk memastikan baseline hijau sebelum mulai (tidak ada file diubah di task ini)

**Checkpoint**: Baseline project terverifikasi bersih sebelum perubahan dimulai.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Skema data (`contact_submissions` baru, `BrandSettings` diperluas) yang dipakai SEMUA user story.

**⚠️ CRITICAL**: T002–T005 harus selesai sebelum task apa pun di Phase 3–5 (US1–US3) dimulai.

- [X] T002 Buat migration `database/migrations/xxxx_create_contact_submissions_table.php` (`name`, `phone`, `topic` nullable, `message`, `status` default `new`, timestamps) sesuai [data-model.md](./data-model.md#contact-submission-baru)
- [X] T003 [P] Buat model & factory `app/Models/ContactSubmission.php` + `database/factories/ContactSubmissionFactory.php`
- [X] T004 Buat settings-migration `database/settings/xxxx_add_whatsapp_and_notification_email_to_brand_settings.php` menambah `whatsapp_number` dan `contact_notification_email` (nullable) ke group `brand`, lalu tambahkan properti yang sama di `app/Settings/BrandSettings.php` dan jalankan `php artisan migrate`
- [X] T005 [P] Tambahkan Section "Kontak & Notifikasi" (field `whatsapp_number`, `contact_notification_email`) ke `app/Filament/Pages/BrandSettingsPage.php` (form + `mount()`/`save()`) sesuai [contracts/admin-panel-surface.md](./contracts/admin-panel-surface.md#brand-settings-perluasan--whatsapp--email-notifikasi) (depends on T004)

**Checkpoint**: Skema `contact_submissions` + field Brand Settings siap — US1–US3 bisa mulai dikerjakan.

---

## Phase 3: User Story 1 - Pengunjung mengirim pesan lewat form Kontak (Priority: P1) 🎯 MVP

**Goal**: Form Kontak benar-benar menyimpan submission ke database, menampilkan konfirmasi sukses tanpa reload, dan mengarahkan ke WhatsApp bila nomor bisnis dikonfigurasi.

**Independent Test**: Buka `/kontak` tanpa login, isi form dengan data valid, submit, verifikasi konfirmasi sukses dan baris baru di `contact_submissions`. Submit dengan field kosong, verifikasi ditolak dengan pesan validasi.

### Tests for User Story 1

- [ ] T006 [P] [US1] Feature test: `POST /kontak` dengan data valid mengembalikan 201, submission tersimpan; field wajib kosong/format phone tidak valid mengembalikan 422 dan TIDAK tersimpan, di `tests/Feature/Pages/ContactPageTest.php` (perluas file yang sudah ada dari 002)
- [ ] T007 [P] [US1] Feature test: response sukses menyertakan `whatsapp_url` berisi nomor & pesan pre-filled ketika `whatsapp_number` terkonfigurasi; `whatsapp_url` bernilai `null` ketika belum dikonfigurasi, di `tests/Feature/Pages/ContactPageTest.php`
- [ ] T008 [P] [US1] Feature test: submission ke-6 dalam 1 menit dari IP yang sama mengembalikan 429 (rate limit), di `tests/Feature/Pages/ContactPageTest.php`

### Implementation for User Story 1

- [ ] T009 [US1] Tambahkan method `store(Request $request)` di `app/Http/Controllers/Public/ContactController.php`: validasi (FR-002), simpan `ContactSubmission`, bangun `whatsapp_url` dari `BrandSettings::whatsapp_number` + pesan ter-encode (FR-012/FR-013, null bila kosong), dispatch notifikasi (lihat US3, T014) tanpa menunggu hasil, return JSON 201 (depends on T003, T004)
- [ ] T010 [US1] Daftarkan route `POST /kontak` di `routes/web.php` dengan middleware `throttle:5,1` (FR-004; depends on T009)
- [ ] T011 [US1] Update `resources/views/pages/kontak.blade.php`: method `submit()` Alpine memanggil `fetch('/kontak', {...})` sungguhan (menggantikan `this.submitted = true` palsu), tangani response sukses (tampilkan konfirmasi + `window.open(whatsapp_url)` bila ada, sertakan link fallback yang sama sebagai tombol) dan response 422 (tampilkan `errors` per field) (FR-003, FR-012, edge case pop-up blocker; depends on T010)

**Checkpoint**: User Story 1 selesai — form Kontak berfungsi penuh end-to-end (simpan + WA redirect), bisa didemo/dirilis sebagai MVP.

---

## Phase 4: User Story 2 - Admin melihat & mengelola daftar pesan masuk (Priority: P1)

**Goal**: Admin bisa melihat, memfilter berdasarkan status, mengubah status, dan menghapus submission lewat panel admin.

**Independent Test**: Login sebagai admin, buka `/admin/contact-submissions`, verifikasi submission dari US1 muncul lengkap. Ubah status, filter berdasarkan status, hapus salah satu — verifikasi masing-masing tersimpan/berefek.

**Depends on**: Phase 2 (Foundational). Tidak bergantung fungsional pada US1 (bisa test pakai factory), tapi secara alami diuji bersama data dari US1 saat quickstart manual.

### Tests for User Story 2

- [ ] T012 [P] [US2] Feature test: admin bisa melihat daftar submission dengan seluruh detail, mengubah status, memfilter berdasarkan status, dan menghapus submission, di `tests/Feature/Admin/ContactSubmissionResourceTest.php`

### Implementation for User Story 2

- [ ] T013 [US2] Buat `app/Filament/Resources/ContactSubmissionResource.php` + `Pages/{ListContactSubmissions,EditContactSubmission}.php` — table: kolom nama/phone/topic/status/waktu masuk, filter status, aksi edit+delete; form edit: field `status` (Select: Baru/Sudah Dihubungi/Selesai), field lain read-only (depends on T003)

**Checkpoint**: User Story 2 selesai — admin bisa kelola submission penuh, independen dari US3.

---

## Phase 5: User Story 3 - Admin mendapat notifikasi otomatis saat ada pesan baru (Priority: P2)

**Goal**: Setiap submission baru memicu email notifikasi ter-queue ke alamat yang dikonfigurasi, tanpa membuat kegagalan pengiriman berdampak ke submission/pengunjung.

**Independent Test**: Konfigurasikan email notifikasi di Brand Settings, submit form Kontak, verifikasi job notifikasi ter-dispatch (test) / email masuk ke log (manual). Kosongkan email, submit lagi — verifikasi tidak ada job yang di-dispatch dan submission tetap sukses.

**Depends on**: Phase 2 (Foundational, field `contact_notification_email`) dan T009 (US1 — titik pemicu dispatch notifikasi).

### Tests for User Story 3

- [ ] T014 [P] [US3] Feature test: `Notification::fake()` — submission baru dengan `contact_notification_email` terkonfigurasi men-dispatch `NewContactSubmission` ke alamat tsb; tanpa `contact_notification_email`, tidak ada notifikasi ter-dispatch; submission tetap 201 di kedua kasus, di `tests/Feature/Pages/ContactPageTest.php`

### Implementation for User Story 3

- [ ] T015 [US3] Buat `app/Notifications/NewContactSubmission.php` (`implements ShouldQueue`, `via(): ['mail']`, `toMail()` berisi ringkasan nama/kontak/topik/pesan)
- [ ] T016 [US3] Di `ContactController@store` (T009), dispatch `Notification::route('mail', $email)->notify(new NewContactSubmission($submission))` hanya bila `BrandSettings::contact_notification_email` terisi (FR-008, FR-009, FR-010; depends on T015)

**Checkpoint**: User Story 3 selesai — seluruh scope AMC-216 (submit, admin CRUD, notifikasi, WA redirect) lengkap.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Verifikasi akhir lintas story sebelum dianggap selesai.

- [ ] T017 [P] Jalankan `vendor/bin/pint --dirty --format agent` untuk merapikan seluruh file PHP yang diubah
- [ ] T018 Jalankan test yang relevan: `php artisan test --compact --filter=ContactPageTest`, `--filter=ContactSubmissionResourceTest`
- [ ] T019 Jalankan `php artisan test --compact` penuh untuk memastikan tidak ada regresi ke test lain (001, 002, 003)
- [ ] T020 `php artisan migrate:fresh --seed` di lokal untuk memastikan migration baru jalan bersih dari kosong
- [ ] T021 Ikuti langkah verifikasi manual di [quickstart.md](./quickstart.md) untuk ketiga user story + WA redirect

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependency — mulai langsung
- **Foundational (Phase 2)**: Memblokir SEMUA user story
- **US1 (Phase 3)**: Butuh Phase 2 selesai; tidak bergantung pada US2/US3
- **US2 (Phase 4)**: Butuh Phase 2 selesai; tidak bergantung pada US1/US3 secara fungsional
- **US3 (Phase 5)**: Butuh Phase 2 selesai DAN T009 (US1) sudah ada sebagai titik pemicu dispatch
- **Polish (Phase 6)**: Setelah story yang ingin dirilis selesai

### Parallel Opportunities

- T003 & T005 (Foundational) paralel — file berbeda
- T006, T007, T008 (test US1) paralel — file sama tapi test case berbeda, ditulis paralel eksekusi sequential
- T012 (test US2) independen, bisa paralel dengan pekerjaan US1/US3 developer lain
- T014 (test US3) independen setelah T009 (US1) ada
- **Antar story**: US2 bisa dikerjakan penuh paralel dengan US1/US3 oleh developer berbeda (tidak berbagi file selain `BrandSettings`/migration dari Foundational); US3 butuh T009 (US1) sudah ada sebelum bisa diselesaikan penuh (titik dispatch notifikasi ada di controller yang sama)

---

## Parallel Example: Mengerjakan US1 dan US2 sekaligus (2 developer, setelah Foundational)

```bash
# Developer A — User Story 1 (submit + WA redirect)
Task: "T006–T011: endpoint submit, rate limit, update Alpine fetch()"

# Developer B — User Story 2 (admin resource)
Task: "T012–T013: ContactSubmissionResource"

# US3 menyusul setelah T009 (Developer A) selesai
Task: "T014–T016: Notification class + dispatch"
```

---

## Implementation Strategy

### MVP First (User Story 1 + 2)

1. Phase 1: Setup
2. Phase 2: Foundational (skema `contact_submissions` + Brand Settings)
3. Phase 3: User Story 1 (submit + WA redirect)
4. Phase 4: User Story 2 (admin lihat & kelola)
5. **STOP & VALIDATE**: Test independen US1+US2 sesuai Independent Test di atas
6. Demo/rilis — lead dari form Kontak sudah tersimpan & terlihat admin, tidak hilang lagi

### Incremental Delivery

1. Phase 1 → Phase 2 (Foundational) → Phase 3+4 (US1+US2) → validasi → rilis (MVP)
2. Phase 5 (US3, notifikasi email) → validasi → rilis
3. Phase 6: Polish setelah ketiganya rilis

### Catatan Constitution

- Setiap story WAJIB punya feature test sebelum ditandai selesai di tracker (Principle IV) — lihat T006–T008, T012, T014.
- Tidak ada dependency baru ditambahkan (Principle V) — Notification bawaan Laravel + `ShouldQueue`, `throttle` middleware bawaan, WA lewat link `wa.me`.
- Kredensial/konfigurasi (nomor WA, email notifikasi) MUST per instalasi via Brand Settings (Principle I) — jangan hardcode di controller/view mana pun.

---

## Notes

- [P] = file berbeda / bagian independen, tidak saling bergantung
- [Story] memetakan task ke user story untuk traceability ke Linear (AMC-216 = seluruh fitur ini; US1–US3 adalah breakdown internal)
- Commit setelah setiap task atau kelompok task logis
- Berhenti di checkpoint mana pun untuk validasi story secara independen
