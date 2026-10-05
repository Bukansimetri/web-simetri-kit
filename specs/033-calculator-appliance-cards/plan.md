# Implementation Plan: Perapian Kartu Peralatan di Kalkulator

**Branch**: `033-calculator-appliance-cards` | **Date**: 2026-10-05 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/033-calculator-appliance-cards/spec.md`

## Summary

Perapian tampilan murni di satu blok Blade: kartu peralatan metode "Berdasarkan Peralatan" pada `resources/views/components/sections/calculator.blade.php` (baris ±78–105). Tanpa perubahan data, model, pengaturan, atau logika Alpine.

- **Akar masalah**: pembungkus pengatur jumlah (`flex items-center … h-10`) tidak punya lebar tetap dan boleh menyusut (flex-shrink bawaan), sedangkan grup kiri (ikon + nama) boleh melebar. Lebar akhir stepper karena itu bergantung pada panjang nama alat. Input angka (`w-8`) juga mewarisi padding dan tombol spin bawaan peramban.
- **Perbaikan**: stepper diberi lebar dan tinggi tetap (`shrink-0 w-20 h-8`, 80 × 32 px), grup kiri `min-w-0 flex-1` sehingga nama yang menyesuaikan, nama dibatasi dua baris (`line-clamp-2`) dengan `title`, dan teks diperkecil/diringankan (`text-xs font-medium`, watt `text-[11px] font-normal`).
- **Area sentuh**: tombol tampil 28 × 32 px tetapi area sentuhnya diperluas ke 32 × 32 px lewat pseudo-elemen.
- **Verifikasi**: tes Feature memeriksa kelas pada markup; ukuran nyata diukur di browser pada 5 nama alat dan 2 lebar layar.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Blade, Alpine (sudah ada), Tailwind. Tanpa dependency baru

**Storage**: N/A (tanpa perubahan data)

**Testing**: PHPUnit 12 (render Blade + pemeriksaan kelas), verifikasi ukuran dengan browser headless

**Target Platform**: Web publik (ponsel 320 px sampai desktop)

**Project Type**: Web application Laravel monolit

**Performance Goals**: Tidak ada dampak

**Constraints**:
- Tampilan lain di kalkulator, perhitungan, dan data tidak berubah.
- Area sentuh tombol ≥ 32 × 32 px.
- Perubahan harus membutuhkan `npm run build` (kelas Tailwind baru).

**Scale/Scope**: 1 berkas view, 1 berkas tes baru

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | Tanpa data atau teks khusus klien. |
| II. White-Label by Default | ✅ | Tidak ada branding. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | Hanya gaya kartu bawaan, tidak ada kontrol tata letak untuk admin. |
| IV. Module Test Coverage | ✅ | Tes Feature untuk markup kartu dan perilaku render peralatan (ikon bawaan dan kustom, nama panjang). |
| V. Simplicity & Dependency Discipline | ✅ | Hanya kelas utilitas Tailwind; tanpa CSS kustom atau dependency. |
| Deployment: dokumentasi | ✅ | Tidak ada fitur admin baru, manual operator tidak berubah. |

**Post-design re-check**: tetap lulus.

## Project Structure

### Documentation (this feature)

```text
specs/033-calculator-appliance-cards/
├── plan.md
├── research.md
├── quickstart.md
├── contracts/card-layout.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

(Tanpa `data-model.md`: tidak ada entitas atau skema baru.)

### Source Code (repository root)

```text
resources/views/components/sections/calculator.blade.php   # ubah: blok kartu peralatan saja
tests/Feature/Pages/CalculatorApplianceCardsTest.php       # baru
```

**Structure Decision**: Tidak ada struktur baru; satu komponen Blade yang sudah ada.

## Complexity Tracking

Tidak ada pelanggaran konstitusi.
