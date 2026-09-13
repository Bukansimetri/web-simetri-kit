# Implementation Plan: Deployment Documentation

**Branch**: `021-deployment-documentation` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/021-deployment-documentation/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Satu dokumen deployment (`docs/deployment.md`) dengan jalur terpisah untuk **VPS** (akses root, LEMP/LAMP dari nol, queue worker persisten via Supervisor, cron untuk scheduler) dan **shared hosting cPanel** (tanpa asumsi SSH, tanpa proses persisten — queue diproses lewat cron `queue:work --stop-when-empty`, document root diarahkan ke `public/` via fitur cPanel atau симlink), plus checklist terpisah untuk setup GA4 (property + service account credentials, mengisi `ANALYTICS_PROPERTY_ID` & `storage/app/analytics/service-account-credentials.json`) dan checklist go-live final lintas platform. Tidak ada perubahan kode aplikasi — murni dokumentasi, konsisten dengan pola 020-client-versioning-strategy.

## Technical Context

**Language/Version**: N/A untuk kode aplikasi — dokumentasi proses deployment (referensi teknis: PHP 8.3, per `composer.json`)

**Primary Dependencies**: Tidak ada dependency baru. Dokumentasi merujuk stack yang sudah given: PHP 8.3 + ekstensi standar (mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, fileinfo, **GD** — dipakai `App\Support\ImageUploads` untuk konversi WebP, lihat research.md #4), MySQL/MariaDB, Composer, Node/npm (build asset saat deploy), Supervisor (VPS saja, untuk queue worker persisten), cron (kedua platform)

**Storage**: N/A (dokumentasi) — tapi mendokumentasikan kebutuhan `storage:link` (symlink `public/storage` → `storage/app/public`, dipakai seluruh upload media: produk, portfolio, banner, dst.)

**Testing**: Tidak ada test otomatis kode — verifikasi lewat eksekusi manual end-to-end prosedur (Independent Test tiap user story di spec.md), sama seperti 020-client-versioning-strategy

**Target Platform**: Dua kelas lingkungan hosting: (1) VPS Linux generik (Ubuntu, akses root — Hostinger VPS dsb. sebagai contoh representatif), (2) shared hosting berbasis cPanel (Niagahoster, DomaiNesia, Hostinger Shared, dsb. sebagai contoh representatif)

**Project Type**: N/A (dokumentasi proses/operasional, bukan kode aplikasi Laravel)

**Performance Goals**: N/A

**Constraints**: MUST tidak mengasumsikan akses SSH/root tersedia di jalur shared hosting (FR-003); MUST menyediakan jalur kerja untuk kebutuhan proses latar belakang di lingkungan tanpa proses persisten (FR-005)

**Scale/Scope**: Satu dokumen deployment + satu checklist GA4 + satu checklist go-live; tidak ada perubahan kode aplikasi

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability** — PASS. Dokumentasi ini justru infrastruktur pendukung langsung bagi prinsip ini: memastikan kit yang sama bisa di-deploy berulang ke berbagai klien di berbagai jenis hosting secara konsisten.
- **II. White-Label by Default** — N/A. Tidak menyentuh branding.
- **III. Settings-Driven Theming, No Page Builder** — N/A. Tidak menyentuh theming/kode aplikasi.
- **IV. Module Test Coverage** — N/A. Tidak ada kode/"content module" yang dibuat.
- **V. Simplicity & Dependency Discipline** — PASS. Tidak ada dependency baru; solusi shared-hosting (cron untuk queue) memakai kapabilitas cPanel bawaan (Cron Jobs), bukan tooling tambahan.
- **Deployment & Client Setup Standards** (bagian konstitusi di luar 5 prinsip inti) — Fitur ini MEMENUHI baris eksplisit "Deployment documentation (server requirements, deploy steps, .env setup, go-live checklist) MUST be kept up to date as modules are added; undocumented modules are considered incomplete" — ini deliverable dokumen tsb.

**Initial gate result**: PASS, tanpa pelanggaran — tabel Complexity Tracking tidak diperlukan.

## Project Structure

### Documentation (this feature)

```text
specs/021-deployment-documentation/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command) — kontrak checklist
├── quickstart.md        # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

(Tidak ada `data-model.md` — tidak ada entitas data aplikasi, lihat spec.md "Key Entities".)

### Source Code (repository root)

```text
docs/
├── deployment.md                 # NEW - requirement server, langkah deploy VPS & cPanel shared hosting
├── checklist-ga4-setup.md        # NEW - checklist setup Google Analytics (US3)
└── checklist-go-live.md          # NEW - checklist go-live final lintas platform (US4)

README.md                          # MODIFIED - tautan ke docs/deployment.md (konsisten dengan pola 020)
```

**Structure Decision**: Tiga dokumen terpisah (bukan satu dokumen raksasa) di `docs/` — sudah ada dari 020-client-versioning-strategy. Pemisahan ini disengaja: `deployment.md` dibaca sekali per deploy (panjang, teknis, berbeda jalur VPS/cPanel), sedangkan kedua checklist dibaca berulang tiap onboarding klien dan perlu bisa di-scan cepat sebagai daftar centang — mencampurnya ke satu file akan membuat checklist tenggelam di tengah narasi teknis yang panjang.

## Complexity Tracking

> Tidak ada pelanggaran Constitution Check — tabel ini dikosongkan (tidak ada baris).
