---

description: "Task list for Client Versioning Strategy implementation"
---

# Tasks: Client Versioning Strategy

**Input**: Design documents from `/specs/020-client-versioning-strategy/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [contracts/git-workflow-contract.md](./contracts/git-workflow-contract.md), [quickstart.md](./quickstart.md)

**Tests**: No automated tests — this feature is a process/documentation decision (Git & GitHub workflow), not application code. Verification is manual, end-to-end execution of the documented procedure, per the "Independent Test" in each user story below.

**Organization**: Tasks are grouped by user story (US1, US2, US3 from spec.md) to enable independent verification of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- File paths are relative to the repository root

## Path Conventions

New `docs/` directory at repository root (doesn't exist yet) for the deliverable document; a GitHub repository setting change; a short README addition.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Repository-level prerequisite both procedures depend on

- [X] T001 ~~Enable "Template repository" in GitHub repo settings~~ — **superseded during implementation**: manual verification (research.md #6) found that GitHub's "Use this template" feature produces an unrelated git history, causing every differing file to show as a false merge conflict on first sync. The chosen mechanism is a plain `git clone` instead (no GitHub repo setting needed) — see [contracts/git-workflow-contract.md](./contracts/git-workflow-contract.md) §1.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The document that carries the decision and both procedures — everything else references it

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T002 Create `docs/versioning-strategi-klien.md` with the chosen strategy statement (template repo + `upstream` remote, per research.md #1) and document structure (sections for: create new client repo, apply upstream updates, bring a client fix back to Simetri) — content for each section filled in by the corresponding user story tasks below

**Checkpoint**: Document skeleton exists — user story content can now be written and verified.

---

## Phase 3: User Story 1 - Membuat repositori klien baru dari Simetri (Priority: P1) 🎯 MVP

**Goal**: Prosedur langkah-demi-langkah yang terdokumentasi untuk membuat repositori klien baru dari Simetri, menghasilkan repo independen dengan remote `upstream` terpasang.

**Independent Test**: Ikuti prosedur yang didokumentasikan untuk membuat repositori klien baru dari Simetri; verifikasi hasilnya adalah repositori kerja independen yang tetap tertaut ke Simetri sebagai sumber pembaruan.

### Implementation for User Story 1

- [X] T003 [US1] Tulis bagian "Membuat repositori klien baru" di `docs/versioning-strategi-klien.md` per [contracts/git-workflow-contract.md](./contracts/git-workflow-contract.md) §2 — **direvisi saat implementasi**: langkah aktual adalah `git clone` Simetri + `git remote rename origin upstream` + `git remote add origin <repo-klien>` (bukan "Use this template", lihat T001), lanjut ke `app:setup-client`
- [X] T004 [US1] Tambahkan tautan singkat ke `docs/versioning-strategi-klien.md` di `README.md` (FR-007 — mudah ditemukan)

### Manual Verification for User Story 1

- [X] T005 [US1] ~~Verifikasi via "Use this template"~~ — **dijalankan via simulasi lokal** (dua repo Git kecil, tanpa akses GitHub — lihat quickstart.md & research.md #6): `git clone` + `git remote rename origin upstream` dikonfirmasi menghasilkan histori bersama (common ancestor) dengan Simetri, prasyarat sehat untuk merge yang benar di T008/T009. Verifikasi end-to-end dengan repo GitHub klien SUNGGUHAN (bukan simulasi) masih perlu dilakukan tim sebelum klien pertama live — lihat quickstart.md poin 4.

**Checkpoint**: MVP complete — siapa pun dapat membuat repositori klien baru mengikuti dokumentasi, terverifikasi end-to-end.

---

## Phase 4: User Story 2 - Menerapkan pembaruan Simetri ke instalasi klien yang sudah berjalan (Priority: P1)

**Goal**: Prosedur terdokumentasi untuk menarik pembaruan Simetri ke repo klien yang sudah dikustomisasi, dengan konflik yang harus diselesaikan sadar oleh developer.

**Independent Test**: Buat satu perubahan contoh di Simetri, terapkan ke instalasi klien yang sudah dikustomisasi menggunakan prosedur yang didokumentasikan; verifikasi perubahan masuk tanpa menghapus kustomisasi klien.

### Implementation for User Story 2

- [X] T006 [US2] Tulis bagian "Menerapkan pembaruan dari Simetri" di `docs/versioning-strategi-klien.md` per [contracts/git-workflow-contract.md](./contracts/git-workflow-contract.md) §3 — **direvisi saat implementasi**: `git fetch upstream` + `git merge upstream/main` biasa (tanpa `--allow-unrelated-histories`, tidak dibutuhkan lagi karena histori sudah bersambung sejak T003 — lihat research.md #6), penanganan konflik manual, langkah verifikasi pasca-merge (`composer install`/`migrate`/test)
- [X] T007 [US2] Tulis bagian "Membawa perbaikan dari klien kembali ke Simetri" di `docs/versioning-strategi-klien.md` per [contracts/git-workflow-contract.md](./contracts/git-workflow-contract.md) §4 (FR-008 — PR manual ke repo Simetri)

### Manual Verification for User Story 2

- [X] T008 [P] [US2] Dijalankan via simulasi lokal (research.md #6): commit satu perubahan di file berbeda pada masing-masing sisi, `git fetch upstream` + `git merge upstream/main` (tanpa flag khusus, sesuai T006). **Temuan**: pendekatan awal ("Use this template" + `--allow-unrelated-histories`) GAGAL di sini — menandai kedua file sebagai konflik "add/add" meski masing-masing hanya diubah satu sisi. Pendekatan revisi (`git clone`, T003) lolos: kedua perubahan masuk bersih tanpa konflik.
- [X] T009 [P] [US2] Dijalankan via simulasi lokal (research.md #6): edit BARIS YANG SAMA di file yang sama pada kedua sisi, lalu merge. Dikonfirmasi Git menandai konflik dengan jelas dan berhenti sampai diselesaikan manual — juga dikonfirmasi bahwa flag `-X ours`/`-X theirs` (dicoba sebagai potensi solusi cepat) TIDAK aman karena diam-diam membuang perubahan sah di sisi yang kalah; flag tsb sengaja TIDAK direkomendasikan di dokumen final.

**Checkpoint**: Kedua alur inti (buat klien baru, terapkan pembaruan) terdokumentasi dan terverifikasi end-to-end, termasuk penanganan konflik.

---

## Phase 5: User Story 3 - Prosedur terdokumentasi tersedia sebelum klien pertama dibuat (Priority: P2)

**Goal**: Anggota tim yang belum pernah terlibat dapat mengikuti dokumentasi saja dan berhasil membuat repo klien baru serta menerapkan satu pembaruan contoh, tanpa bantuan langsung.

**Independent Test**: Seorang anggota tim yang belum pernah terlibat dapat mengikuti dokumentasi dan berhasil membuat repositori klien baru serta menerapkan satu pembaruan contoh, tanpa bertanya langsung ke pembuat keputusan awal.

### Implementation for User Story 3

- [X] T010 [US3] Review `docs/versioning-strategi-klien.md` selesai: satu strategi dinyatakan jelas di bagian "Keputusan" di bagian atas dokumen (bukan beberapa opsi terbuka — FR-001), termasuk alasan penolakan kedua alternatif (Composer package, "Use this template"); setiap langkah di §1–§3 dapat diikuti berdiri sendiri tanpa perlu membaca `specs/` terlebih dahulu

### Manual Verification for User Story 3

- [ ] T011 [US3] Jalankan verifikasi manual quickstart.md langkah 3: minta satu anggota tim yang belum pernah membuat repo klien untuk membaca hanya `docs/versioning-strategi-klien.md`, lalu mempraktikkan pembuatan repo klien uji coba + satu penerapan pembaruan contoh tanpa bantuan tambahan; catat keberhasilan dan waktu yang dibutuhkan (SC-003)

**Checkpoint**: Dokumentasi terbukti dapat dipahami dan diikuti mandiri oleh orang yang belum terlibat sebelumnya.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependensi — pengaturan GitHub repo, bisa dilakukan kapan saja sebelum US1
- **Foundational (Phase 2)**: Bergantung pada Setup — BLOCKS seluruh user story (dokumen skeleton harus ada sebelum diisi)
- **User Story 1 (Phase 3)**: Bergantung pada Foundational — tidak bergantung pada US2/US3
- **User Story 2 (Phase 4)**: Bergantung pada Foundational; verifikasi manualnya (T008–T009) bergantung pada repo uji coba dari T005 (US1) — secara nilai bisnis dokumentasinya independen, tapi pengujian end-to-end memang butuh urutan T005 dulu
- **User Story 3 (Phase 5)**: Bergantung pada US1 & US2 selesai (review menyeluruh + uji coba orang lain butuh dokumen yang sudah lengkap)

### Parallel Opportunities

- T003–T004 (US1, file berbeda: docs vs README) dapat paralel
- T008–T009 (verifikasi manual US2, skenario independen) dapat paralel bila dikerjakan dua orang, atau berurutan bila satu orang

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (aktifkan template repository)
2. Complete Phase 2: Foundational (skeleton dokumen)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: repo klien uji coba berhasil dibuat mengikuti dokumentasi
5. Ini sudah cukup untuk memotong repo klien pertama yang sesungguhnya bila mendesak (meski US2 — jalur update — sebaiknya tetap diselesaikan sebelum klien pertama live, karena itulah alasan utama fitur ini ada)

### Incremental Delivery

1. Setup + Foundational → skeleton dokumen siap
2. Tambah User Story 1 → repo klien baru bisa dibuat konsisten → demo/pakai (MVP)
3. Tambah User Story 2 → pembaruan bisa diterapkan tanpa kehilangan kustomisasi → strategi lengkap dipakai produksi
4. Tambah User Story 3 → terbukti dapat diikuti mandiri oleh anggota tim baru

---

## Notes

- No PHP/test files are touched by this feature — all tasks operate on `docs/`, `README.md`, and GitHub repository settings
- Constitution's Deployment & Client Setup Standards explicitly requires this decision to be made and documented before any client repo is cut — this feature directly satisfies that requirement
- Commit `docs/versioning-strategi-klien.md` and the README change together as this feature's only code-repository changes
