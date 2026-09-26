# Implementation Plan: Dashboard Prospek Admin

**Branch**: `027-leads-dashboard` | **Date**: 2026-09-26 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/027-leads-dashboard/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Mengganti dua widget bawaan Filament di halaman awal panel admin dengan tiga widget prospek: kartu statistik (pesan masuk baru, lead kalkulator baru, prospek 30 hari dengan sparkline, konversi 90 hari), tabel gabungan "Perlu ditindaklanjuti" (10 prospek baru terbaru dengan penanda terlambat > 48 jam), dan grafik bar 12 minggu per sumber. Perhitungan dipusatkan di satu service yang memakai zona waktu dari Pengaturan Umum situs. Tidak ada tabel, migrasi, atau dependency baru; hanya widget Filament 3.3 bawaan.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13.23

**Primary Dependencies**: Filament 3.3 (StatsOverviewWidget, ChartWidget, Widget; Chart.js dibundel Filament), Spatie Laravel Settings 3.9 (zona waktu dari `SiteSettings`). Tidak ada dependency baru

**Storage**: MySQL (produksi), SQLite in-memory (test). Tabel yang dibaca: `contact_submissions`, `calculator_leads`. Tidak ada perubahan skema

**Testing**: PHPUnit 12 feature test di `tests/Feature/Dashboard/`, dengan `travelTo` untuk mengunci waktu dan factory model

**Target Platform**: Panel admin Filament di `/admin`

**Project Type**: Aplikasi web Laravel + Filament (monolit)

**Performance Goals**: Dashboard terbuka < 2 detik dengan 5.000 prospek (SC-005): maksimal ~8 query ringan, data berkala hanya mengambil kolom `created_at` dalam rentang waktu

**Constraints**: Zona waktu dari `SiteSettings::timezone`, bukan `config('app.timezone')` (UTC); akses mengikuti `canViewAny()` resource lead; `getHeading()` widget tanpa query (kompatibel Filament Shield)

**Scale/Scope**: 1 service, 3 widget + 1 view Blade, 1 factory baru, perubahan kecil di `AdminPanelProvider`, 4 file test

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability**: PASS. Zona waktu dibaca dari pengaturan situs, bukan hardcode. Tidak ada data klien di kode. Widget bekerja pada instalasi kosong (edge case "tanpa prospek").
- **II. White-Label by Default**: PASS. Menghapus widget info Filament (identitas vendor panel) dari dashboard; label widget netral dan berbahasa Indonesia.
- **III. Settings-Driven Theming, No Page Builder**: N/A. Tidak menyentuh tema publik; warna widget memakai warna panel yang sudah mengikuti pengaturan Tampilan.
- **IV. Module Test Coverage**: PASS. Feature test untuk service, tiap widget, akses, dan halaman dashboard (lihat Project Structure).
- **V. Simplicity & Dependency Discipline**: PASS. Hanya komponen Filament bawaan; paket trend (`flowframe/laravel-trend`) sengaja tidak dipakai (research.md §3). Satu service tanpa abstraksi tambahan.

**Initial gate result**: PASS. Complexity Tracking tidak diperlukan.

**Post-design re-check**: PASS. Desain di research.md, data-model.md, dan contracts/dashboard-ui.md tidak menambah skema, dependency, atau konfigurasi per klien baru.

## Project Structure

### Documentation (this feature)

```text
specs/027-leads-dashboard/
├── plan.md                    # This file
├── research.md                # Phase 0: keputusan teknis
├── data-model.md              # Phase 1: sumber data & nilai turunan
├── quickstart.md              # Phase 1: cara verifikasi
├── contracts/
│   └── dashboard-ui.md        # Phase 1: kontrak tampilan widget
├── checklists/requirements.md
└── tasks.md                   # Phase 2 (/speckit-tasks)
```

### Source Code (repository root)

```text
app/
├── Services/
│   └── LeadDashboardMetrics.php            # BARU: semua perhitungan, zona waktu situs
├── Filament/Widgets/                        # BARU (folder sudah di-discover panel)
│   ├── LeadStatsOverview.php                # StatsOverviewWidget, sort 1 (US1)
│   ├── LeadsNeedingFollowUp.php             # Widget + view, sort 2 (US2)
│   └── WeeklyLeadsChart.php                 # ChartWidget bar, sort 3 (US3)
└── Providers/Filament/AdminPanelProvider.php  # UBAH: hapus AccountWidget & FilamentInfoWidget

resources/views/filament/widgets/
└── leads-needing-follow-up.blade.php        # BARU: tabel gabungan

database/factories/
└── CalculatorLeadFactory.php                # BARU: belum ada, dibutuhkan test & quickstart

tests/Feature/Dashboard/
├── LeadDashboardMetricsTest.php             # BARU: angka, batas waktu, zona waktu, konversi
├── LeadStatsOverviewTest.php                # BARU: nilai & URL kartu (US1)
├── LeadsNeedingFollowUpTest.php             # BARU: isi, urutan, terlambat, kosong (US2)
├── WeeklyLeadsChartTest.php                 # BARU: 12 minggu, dua seri, nol (US3)
└── DashboardPageTest.php                    # BARU: widget bawaan hilang, akses, halaman 200
```

**Structure Decision**: Widget ditempatkan di `app/Filament/Widgets/`, yang sudah terdaftar lewat `discoverWidgets()` di `AdminPanelProvider` sehingga tidak perlu registrasi manual; dashboard bawaan Filament (`Filament\Pages\Dashboard`) otomatis menampilkannya, urut berdasarkan `$sort`. Service mengikuti pola `app/Services/SavingsEstimator.php`.

## Implementation Notes

- **Zona waktu**: `LeadDashboardMetrics` mengambil `app(SiteSettings::class)->timezone` sekali, menghitung batas bawah rentang di zona itu, mengonversinya ke UTC untuk `where('created_at', '>=', ...)`, lalu mengelompokkan `created_at` hasil query per tanggal/minggu lokal di PHP (research.md §2–3).
- **URL filter**: `ContactSubmissionResource::getUrl('index', ['tableFilters' => ['status' => ['value' => ContactSubmission::STATUS_NEW]]])`, dan setara untuk `CalculatorLeadResource` (research.md §5).
- **Akses**: `canView()` tiap widget = `ContactSubmissionResource::canViewAny() && CalculatorLeadResource::canViewAny()` (research.md §6).
- **Shield**: `getHeading()` / `$heading` berupa string tetap; jalankan ulang `tests/Feature/Admin/RoleResourceEditTest.php` setelah widget ditambahkan (research.md §7).
- **Umur relatif**: pakai `Carbon::diffForHumans()` dengan locale `id` (aplikasi sudah `APP_LOCALE=id`).
- **Format Rupiah**: ikuti format yang sudah dipakai `CalculatorLeadResource` (`'Rp '.number_format($n, 0, ',', '.')`).
- **Dampak ke production**: tidak ada migrasi atau dependency; cukup `php artisan optimize:clear` dan `php artisan filament:optimize-clear` setelah pull.

## Complexity Tracking

Tidak ada pelanggaran constitution.
