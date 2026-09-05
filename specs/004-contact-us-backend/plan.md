# Implementation Plan: Contact Us Backend

**Branch**: `004-contact-us-backend` | **Date**: 2026-09-05 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/004-contact-us-backend/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Mengaktifkan submit sungguhan pada form Kontak yang sudah ada dari 002-theme-branding-system: submission tersimpan ke tabel baru `contact_submissions`, memicu notifikasi email ter-queue ke admin, dan mengarahkan pengunjung ke WhatsApp (`wa.me`, pesan pre-filled) bila nomor bisnis dikonfigurasi. Admin bisa melihat, mengubah status, memfilter, dan menghapus submission lewat Filament Resource baru. Nomor WhatsApp & email notifikasi ditambahkan sebagai field baru di `BrandSettings` yang sudah ada.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Tidak ada dependency baru — `Illuminate\Notifications` (bawaan Laravel, `ShouldQueue`), `throttle` middleware bawaan, Filament 3.3 (Resource baru `ContactSubmissionResource`), Alpine.js (`fetch()` menggantikan simulasi sukses palsu di `kontak.blade.php`)

**Storage**: MySQL — migration baru `contact_submissions`, perluasan `settings` (grup `brand`: `whatsapp_number`, `contact_notification_email`)

**Testing**: PHPUnit feature test — HTTP test untuk endpoint submit (`tests/Feature/Pages/ContactPageTest.php`, sudah ada dari 002, diperluas), Livewire test untuk `ContactSubmissionResource` (pola sama seperti `ProductResourceTest`/`CategoryResourceTest` dari 003), `Notification::fake()` untuk verifikasi job notifikasi ter-dispatch tanpa mengirim email sungguhan saat test

**Target Platform**: Server web Laravel standar (sama seperti fitur-fitur sebelumnya), `QUEUE_CONNECTION=database` (sudah dikonfigurasi sejak awal project — worker perlu berjalan di produksi, dicatat di quickstart.md)

**Project Type**: Web application — perluasan langsung dari halaman publik & admin panel yang sudah ada

**Performance Goals**: Response submit form MUST tidak menunggu proses kirim email (notifikasi di-queue, FR-009) — target UX form tetap instan seperti sebelumnya (tanpa reload)

**Constraints**: Rate limiting wajib untuk cegah spam (FR-004); kredensial/konfigurasi (nomor WA, email notifikasi) MUST per instalasi via Brand Settings, bukan hardcode (Principle I, FR-010/FR-013); tidak ada dependency/integrasi berbayar baru (Principle V, FR-012 — WA lewat `wa.me`, bukan API)

**Scale/Scope**: 1 entity baru (`ContactSubmission`) + 1 Filament Resource baru + perluasan `BrandSettings` (2 field) + 1 endpoint submit + 1 Notification class

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Nomor WhatsApp & email notifikasi dikonfigurasi per instalasi lewat Brand Settings, bukan hardcode di kode/view. **PASS** |
| II. White-Label by Default | Tidak langsung | Fitur ini tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Tidak langsung | Bukan fitur theming/layout — tidak relevan, tidak melanggar. |
| IV. Module Test Coverage | Ya | US1 (submit), US2 (admin CRUD submission), US3 (notifikasi) MUST masing-masing punya feature test dasar sebelum dianggap selesai. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Tidak ada dependency baru — Notification bawaan Laravel + `ShouldQueue`, `throttle` middleware bawaan, WA lewat link `wa.me` (bukan API berbayar). **PASS** |

Tidak ada pelanggaran constitution yang butuh entry Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/004-contact-us-backend/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   ├── submit-endpoint.md
│   └── admin-panel-surface.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Models/
│   └── ContactSubmission.php               # Baru
├── Notifications/
│   └── NewContactSubmission.php            # Baru — ShouldQueue, mail channel
├── Settings/
│   └── BrandSettings.php                   # Diperluas: whatsapp_number, contact_notification_email
├── Filament/Resources/
│   ├── ContactSubmissionResource.php       # Baru
│   └── ContactSubmissionResource/Pages/{ListContactSubmissions,EditContactSubmission}.php
├── Filament/Pages/
│   └── BrandSettingsPage.php               # Diperluas: 2 field baru di Section "Kontak & Notifikasi"
└── Http/Controllers/Public/
    └── ContactController.php               # Diperluas: method store() (submit sungguhan)

database/
├── migrations/
│   ├── xxxx_create_contact_submissions_table.php
│   └── xxxx_add_whatsapp_and_notification_email_to_brand_settings.php
└── factories/
    └── ContactSubmissionFactory.php        # Baru

resources/views/pages/kontak.blade.php      # Diperbarui: submit() Alpine memanggil fetch() sungguhan,
                                              # menangani whatsapp_url dari response

routes/web.php                              # Tambah route POST /kontak (throttle middleware)

tests/Feature/
├── Pages/ContactPageTest.php               # Diperluas (sudah ada dari 002): submit sungguhan, validasi, rate limit
└── Admin/ContactSubmissionResourceTest.php # Baru — US2
```

**Structure Decision**: Perluasan langsung dari controller/view publik dan admin panel yang sudah ada — tidak ada folder/namespace baru selain `app/Notifications/` (pertama kali dipakai di project ini, tapi merupakan namespace standar Laravel, bukan struktur custom).

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
