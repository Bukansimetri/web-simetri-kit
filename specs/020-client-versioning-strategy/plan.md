# Implementation Plan: Client Versioning Strategy

**Branch**: `020-client-versioning-strategy` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/020-client-versioning-strategy/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Keputusan: pakai **`git clone` + remote `upstream`** (bukan GitHub "Use this template", dan bukan Composer package private) untuk mendistribusikan Simetri ke tiap instalasi klien. Setiap klien dibuat dengan meng-clone Simetri (histori git penuh ikut terbawa sebagai common ancestor), me-rename remote asal jadi `upstream`, lalu push ke repositori GitHub baru yang kosong. Pembaruan diterima lewat `git fetch upstream` + `git merge upstream/main` secara berkala — TANPA `--allow-unrelated-histories`, karena histori bersama sejak awal membuat merge selalu 3-way yang benar (lihat research.md #6 — rencana awal "Use this template" ditolak setelah verifikasi manual menemukan cacat serius: histori independen membuat SEMUA file berbeda ditandai konflik palsu, bukan hanya yang benar-benar bertabrakan). Deliverable utama fitur ini adalah dokumen `docs/versioning-strategi-klien.md` yang menjadi rujukan tunggal prosedur ini (FR-007), bukan perubahan kode aplikasi.

## Technical Context

**Language/Version**: N/A untuk kode aplikasi — fitur ini adalah keputusan proses & dokumentasi Git/GitHub

**Primary Dependencies**: Git (built-in) saja — `git clone`, `git remote`, `git fetch`, `git merge` standar; tidak ada fitur GitHub khusus (bukan "Template repository"), tidak ada dependency Composer/npm baru

**Storage**: N/A

**Testing**: Tidak ada test otomatis kode — "pengujian" fitur ini adalah menjalankan prosedur end-to-end secara manual (buat repo klien uji coba, terapkan satu pembaruan contoh) sesuai Independent Test di setiap user story spec.md

**Target Platform**: GitHub (hosting repositori organisasi `Bukansimetri`, tempat `web-simetri-kit` — repo ini sendiri — sudah di-hosting)

**Project Type**: N/A (keputusan tooling/proses, bukan kode aplikasi Laravel)

**Performance Goals**: N/A

**Constraints**: MUST tidak mengubah struktur aplikasi Laravel yang sudah ada menjadi bentuk package/library (lihat research.md #1 untuk alasan menolak opsi Composer package)

**Scale/Scope**: Satu dokumen prosedur + verifikasi manual; tidak ada perubahan kode aplikasi

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability** — PASS. Ini justru fitur yang secara langsung menopang prinsip ini di level proses: menjamin Simetri bisa diperbanyak ke banyak klien secara konsisten dan menerima pembaruan lintas klien.
- **II. White-Label by Default** — N/A. Tidak menyentuh branding.
- **III. Settings-Driven Theming, No Page Builder** — N/A. Tidak menyentuh theming/kode aplikasi.
- **IV. Module Test Coverage** — N/A. Tidak ada "content module" kode yang dibuat; deliverable adalah dokumentasi & keputusan proses.
- **V. Simplicity & Dependency Discipline** — PASS. Opsi yang dipilih (`git clone` + remote `upstream`) memakai kapabilitas Git bawaan tanpa dependency baru, dan bahkan lebih sederhana dari rencana awal (tidak perlu `--allow-unrelated-histories`/flag strategi merge khusus, lihat research.md #6); opsi Composer package ditolak karena akan memaksa restrukturisasi aplikasi jadi bentuk package — pelanggaran langsung terhadap prinsip ini untuk manfaat yang tidak lebih baik (lihat research.md #1).
- **Deployment & Client Setup Standards** (bagian konstitusi di luar 5 prinsip inti) — Fitur ini MEMENUHI baris eksplisit "Versioning/update strategy across client forks MUST be explicit ... and documented before any client repo is cut from Simetri" secara langsung; ini adalah keputusan & dokumentasi yang diminta baris tsb.

**Initial gate result**: PASS, tanpa pelanggaran — tabel Complexity Tracking tidak diperlukan.

## Project Structure

### Documentation (this feature)

```text
specs/020-client-versioning-strategy/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command) — kontrak prosedur git
├── quickstart.md        # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

(Tidak ada `data-model.md` — fitur ini tidak melibatkan entitas data aplikasi, lihat spec.md "Key Entities".)

### Source Code (repository root)

```text
docs/
└── versioning-strategi-klien.md   # NEW - deliverable utama: prosedur buat-klien-baru & terapkan-pembaruan (FR-001–FR-008)

README.md                           # MODIFIED - tambah tautan singkat ke docs/versioning-strategi-klien.md agar mudah ditemukan (FR-007)
```

**Structure Decision**: Tidak ada kode aplikasi Laravel yang dibuat/diubah. Satu direktori `docs/` baru (belum ada di repo ini) untuk dokumentasi proses/deployment tingkat proyek — dipisah dari `specs/` (yang berisi artefak spec-kit per fitur, bukan dokumentasi rujukan jangka panjang untuk tim) dan bukan bagian dari `app/`, `database/`, dsb. Ini juga menjadi lokasi yang wajar untuk dokumentasi deployment terkait (AMC-231, fitur terpisah) di masa depan.

## Complexity Tracking

> Tidak ada pelanggaran Constitution Check — tabel ini dikosongkan (tidak ada baris).
