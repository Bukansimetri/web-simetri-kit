---

description: "Task list for Deployment Documentation implementation"
---

# Tasks: Deployment Documentation

**Input**: Design documents from `/specs/021-deployment-documentation/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [contracts/checklist-contract.md](./contracts/checklist-contract.md), [quickstart.md](./quickstart.md)

**Tests**: No automated tests — this feature is documentation/checklist content, not application code. Verification is manual execution of the documented procedures on real hosting (VPS/cPanel), per the "Independent Test" in each user story below; one review-only check (US4) can be done without a server.

**Organization**: Tasks are grouped by user story (US1–US4 from spec.md) to enable independent verification of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- File paths are relative to the repository root

## Path Conventions

Three new documents under `docs/` (already exists from 020-client-versioning-strategy); a short `README.md` addition.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Skeleton for the three deliverable documents

- [X] T001 [P] Create `docs/deployment.md` skeleton with the four sections required by [contracts/checklist-contract.md](./contracts/checklist-contract.md) (Requirement Server; Deploy ke VPS; Deploy ke Shared Hosting cPanel; tautan ke kedua checklist)
- [X] T002 [P] Create `docs/checklist-ga4-setup.md` skeleton (checkbox list, empty)
- [X] T003 [P] Create `docs/checklist-go-live.md` skeleton (checkbox list, empty)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Content shared by both hosting paths — written once, referenced by US1 and US2 instead of duplicated

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T004 Write "Requirement Server" section in `docs/deployment.md` per research.md #1 (PHP 8.3+, extension list including `gd`, MySQL/MariaDB, Apache/Nginx with mod_rewrite) — verified against `composer.json` and actual code usage (`App\Support\ImageUploads`), not generic Laravel boilerplate

**Checkpoint**: Shared requirement baseline documented — platform-specific procedures (US1, US2) can now be written.

---

## Phase 3: User Story 1 - Deploy instalasi klien ke VPS (Priority: P1) 🎯 MVP

**Goal**: Prosedur lengkap dari VPS kosong sampai situs live dengan HTTPS, queue worker persisten, dan scheduler berjalan.

**Independent Test**: Sediakan satu VPS baru (Ubuntu/Linux, tanpa stack apa pun), ikuti dokumen deployment VPS dari awal sampai akhir; verifikasi situs klien dapat diakses publik via domain/HTTPS, dan seluruh fitur yang bergantung pada proses latar belakang berfungsi.

### Implementation for User Story 1

- [X] T005 [US1] Tulis bagian "Deploy ke VPS" di `docs/deployment.md` per research.md #2: instalasi stack dari nol (Nginx/Apache, PHP-FPM 8.3, MySQL, Composer, Node.js), clone repo klien (rujuk `docs/versioning-strategi-klien.md`), `.env` + `app:setup-client` (rujuk `specs/018-setup-client-command/quickstart.md`), `storage:link`, `migrate`, build asset (`npm run build`), konfigurasi virtual host mengarah ke `public/`
- [X] T006 [US1] Tulis sub-bagian konfigurasi proses latar belakang VPS di `docs/deployment.md`: Supervisor untuk `php artisan queue:work --sleep=3 --tries=3` (persisten, auto-restart), cron `* * * * * php artisan schedule:run`
- [X] T007 [US1] Tulis sub-bagian HTTPS VPS di `docs/deployment.md`: instalasi Certbot (Let's Encrypt) + verifikasi auto-renewal aktif (FR-007, edge case perpanjangan sertifikat)

### Manual Verification for User Story 1

- [ ] T008 [US1] Jalankan verifikasi manual quickstart.md poin 1 pada VPS uji coba sungguhan: ikuti `docs/deployment.md` bagian VPS end-to-end; konfirmasi situs live via HTTPS, kirim form kontak uji coba dan konfirmasi notifikasi terkirim (queue worker berjalan), konfirmasi entri log scheduler muncul setelah ≥1 menit

**Checkpoint**: MVP complete — jalur VPS terdokumentasi lengkap dan terverifikasi end-to-end.

---

## Phase 4: User Story 2 - Deploy instalasi klien ke shared hosting cPanel (Priority: P1)

**Goal**: Prosedur lengkap untuk shared hosting cPanel tanpa asumsi SSH/proses persisten, dengan mekanisme alternatif berbasis cron untuk queue & scheduler.

**Independent Test**: Sediakan satu akun shared hosting cPanel baru (tanpa akses root/SSH persisten), ikuti dokumen deployment shared hosting dari awal sampai akhir; verifikasi situs klien dapat diakses publik, dan fitur latar belakang tetap berfungsi lewat cron.

### Implementation for User Story 2

- [X] T009 [US2] Tulis sub-bagian "Cek & pilih versi PHP + ekstensi" di `docs/deployment.md` per research.md #3: langkah via cPanel MultiPHP Manager/Select PHP Version, MUST menandai sebagai blocker bila versi/ekstensi tidak memenuhi syarat (FR-011) sebelum melangkah lebih jauh
- [X] T010 [US2] Tulis sub-bagian "Upload kode aplikasi" di `docs/deployment.md`: jalur dengan SSH (sama seperti VPS, langsung `composer install` di server) DAN jalur tanpa SSH (`composer install --no-dev --optimize-autoloader` di lokal, upload `vendor/` + kode via File Manager/FTP) — edge case dari spec.md (akses SSH tidak tersedia)
- [X] T011 [US2] Tulis sub-bagian "Document root & struktur folder publik" di `docs/deployment.md` per research.md #3: opsi document root kustom ke `public/` (bila cPanel mengizinkan), atau isi `public_html/` dengan isi `public/` + penyesuaian path `require` di `index.php` — MUST menjelaskan cara tidak mengekspos kode sumber/`.env` (FR-003, edge case)
- [X] T012 [US2] Tulis sub-bagian `.env` + `storage:link` shared hosting di `docs/deployment.md`: `app:setup-client` (rujuk 018-setup-client-command), `storage:link` via SSH bila tersedia, alternatif manual/copy bila tidak (research.md #4)
- [X] T013 [US2] Tulis sub-bagian "Queue & Scheduler via Cron Jobs" di `docs/deployment.md` per research.md #3: Cron Job `php artisan queue:work --stop-when-empty` tiap menit, Cron Job `php artisan schedule:run` tiap menit, catatan alternatif `QUEUE_CONNECTION=sync` bila cron sama sekali tidak memungkinkan
- [X] T014 [US2] Tulis sub-bagian HTTPS shared hosting di `docs/deployment.md`: verifikasi AutoSSL aktif untuk domain

### Manual Verification for User Story 2

- [ ] T015 [US2] Jalankan verifikasi manual quickstart.md poin 2 pada akun shared hosting cPanel uji coba sungguhan: ikuti `docs/deployment.md` bagian cPanel end-to-end; konfirmasi situs live tanpa kode ter-expose, kirim form kontak uji coba dan konfirmasi notifikasi terkirim dalam waktu wajar via Cron Job

**Checkpoint**: Kedua jalur hosting utama (VPS, cPanel) terdokumentasi lengkap dan terverifikasi end-to-end.

---

## Phase 5: User Story 3 - Setup Google Analytics sebagai bagian onboarding klien (Priority: P1)

**Goal**: Checklist GA4 yang membuat dashboard Analytics di admin panel benar-benar menampilkan data.

**Independent Test**: Pada satu instalasi klien yang sudah live, ikuti checklist setup GA4; verifikasi dashboard Google Analytics di admin panel menampilkan data dalam waktu wajar setelah traffic mulai masuk.

### Implementation for User Story 3

- [X] T016 [US3] Tulis `docs/checklist-ga4-setup.md` lengkap per research.md #5 dan [contracts/checklist-contract.md](./contracts/checklist-contract.md): property GA4 → service account → unduh credentials JSON → **beri akses viewer ke property** (langkah eksplisit terpisah, paling sering terlewat) → upload credentials ke `storage/app/analytics/service-account-credentials.json` → isi `ANALYTICS_PROPERTY_ID` di `.env` → verifikasi dashboard, dengan catatan varian "klien sudah punya akun GA4" (FR-009) di langkah pertama

### Manual Verification for User Story 3

- [ ] T017 [US3] Jalankan verifikasi manual quickstart.md poin 3 pada instalasi yang sudah live (hasil T008 atau T015): ikuti `docs/checklist-ga4-setup.md`; konfirmasi dashboard Google Analytics di admin panel menampilkan data setelah traffic uji coba masuk, bukan pesan kosong/error

**Checkpoint**: Onboarding klien baru mencakup Analytics yang benar-benar berfungsi, bukan dashboard kosong.

---

## Phase 6: User Story 4 - Checklist go-live final lintas platform (Priority: P2)

**Goal**: Satu checklist akhir yang berlaku untuk kedua platform, dengan setiap butir dapat dijawab ya/tidak tegas.

**Independent Test**: Pada instalasi yang baru selesai di-deploy dan sudah menjalani US3, jalankan checklist go-live; verifikasi setiap butir dapat diperiksa dengan jawaban ya/tidak yang jelas.

### Implementation for User Story 4

- [X] T018 [US4] Tulis `docs/checklist-go-live.md` lengkap per research.md #6 dan [contracts/checklist-contract.md](./contracts/checklist-contract.md): domain/DNS, HTTPS aktif, `APP_ENV=production` & `APP_DEBUG=false`, `APP_URL` sesuai domain final, queue & scheduler terverifikasi jalan, backup database terjadwal, status checklist GA4 — setiap butir ditandai **blocker** atau **boleh ditunda dengan catatan** (FR-011)

### Manual Verification for User Story 4

- [X] T019 [US4] Jalankan verifikasi manual quickstart.md poin 4 (review, tanpa server): telaah `docs/checklist-go-live.md` dan konfirmasi tidak ada butir subjektif/ambigu — setiap butir dapat dijawab ya/tidak murni dari kondisi instalasi

**Checkpoint**: Seluruh siklus deployment (deploy → Analytics → go-live) terdokumentasi lintas kedua platform hosting.

---

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T020 [P] Tambahkan tautan ke `docs/deployment.md` di `README.md` (konsisten dengan pola tautan `docs/versioning-strategi-klien.md` dari 020-client-versioning-strategy)
- [X] T021 Review silang ketiga dokumen: pastikan `docs/deployment.md` mengarah ke kedua checklist di bagian akhir (kontrak §Independensi platform), dan tidak ada duplikasi konten yang tidak sinkron antar dokumen

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependensi — skeleton tiga dokumen
- **Foundational (Phase 2)**: Bergantung pada Setup — BLOCKS US1 & US2 (keduanya butuh baseline requirement server)
- **User Story 1 (Phase 3)**: Bergantung pada Foundational — tidak bergantung pada US2
- **User Story 2 (Phase 4)**: Bergantung pada Foundational — tidak bergantung pada US1 (ditulis di file yang sama, `docs/deployment.md`, tapi bagian yang berbeda — lihat catatan paralel di bawah)
- **User Story 3 (Phase 5)**: Independen secara isi (file terpisah), tapi verifikasi manualnya (T017) butuh instalasi live dari T008/T015 (US1/US2)
- **User Story 4 (Phase 6)**: Independen secara isi (file terpisah); T018 merujuk status GA4 dari US3, sehingga logis ditulis setelah US3
- **Polish (Phase 7)**: Bergantung pada seluruh user story selesai

### Parallel Opportunities

- T001–T003 (Setup, tiga file berbeda) dapat paralel
- T005–T007 (US1) dan T009–T014 (US2) menyentuh file yang SAMA (`docs/deployment.md`) di bagian berbeda — aman dikerjakan berurutan oleh satu orang, TIDAK disarankan benar-benar paralel oleh dua orang tanpa koordinasi (risiko conflict saat commit)
- T016 (US3) dan T018 (US4) — file berbeda dari `deployment.md` dan dari satu sama lain — dapat paralel dengan T005–T014 bila dikerjakan orang berbeda

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: deploy VPS uji coba berhasil end-to-end
5. Ini sudah cukup untuk klien yang memakai VPS; klien shared hosting menunggu US2

### Incremental Delivery

1. Setup + Foundational → requirement server terdokumentasi
2. Tambah User Story 1 → jalur VPS lengkap → dipakai untuk klien VPS pertama (MVP)
3. Tambah User Story 2 → jalur shared hosting cPanel lengkap → dipakai untuk klien budget terbatas
4. Tambah User Story 3 → onboarding GA4 terstandardisasi
5. Tambah User Story 4 → jaring pengaman go-live lintas platform
6. Polish → tautan README, review silang

---

## Notes

- No PHP/test files are touched by this feature — all tasks operate on `docs/` and `README.md`
- Constitution's Deployment & Client Setup Standards explicitly requires deployment documentation to exist and stay current as modules are added — this feature is that deliverable
- Manual verification tasks requiring real VPS/hosting accounts (T008, T015, T017) are flagged as such — they cannot be completed inside a sandboxed development environment and are follow-up actions for whoever provisions the first real client deployment on each platform
