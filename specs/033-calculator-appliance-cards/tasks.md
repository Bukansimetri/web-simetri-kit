---

description: "Task list for Perapian Kartu Peralatan di Kalkulator"
---

# Tasks: Perapian Kartu Peralatan di Kalkulator

**Input**: Design documents from `/specs/033-calculator-appliance-cards/`

**Prerequisites**: plan.md, spec.md, research.md, contracts/card-layout.md, quickstart.md (tanpa data-model.md: tidak ada entitas baru)

**Tests**: Disertakan (Principle IV): tes Feature memeriksa markup kartu, ditambah pengukuran nyata di browser.

**Organization**: Per user story (US1–US4). Semua perubahan ada di satu blok Blade, jadi tugas implementasi dikerjakan berurutan.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat paralel (berkas berbeda)
- **[Story]**: US1–US4
- Berkas utama: `resources/views/components/sections/calculator.blade.php` (blok `Input: By Appliance`, ±baris 78–105)

---

## Phase 1: Setup

- [X] T001 Pastikan branch `033-calculator-appliance-cards` dari `main` terbaru, lalu jalankan `php artisan test --compact` sebagai baseline (catat jumlah lulus/dilewati)

---

## Phase 2: Foundational

- [X] T002 Buat berkas tes `tests/Feature/Pages/CalculatorApplianceCardsTest.php` dengan helper yang merender `/` lalu mengekstrak potongan `<template x-for="item in appliances">` (dengan `LegacyMarkup::extract` atau `preg_match`) dan menyiapkan `ElectricityAppliance` aktif (satu bernama pendek "TV", satu bernama panjang 40 karakter, satu berikon gambar kustom) lewat factory. Pastikan tes dasar lulus terhadap markup saat ini (potongan ditemukan, atribut perilaku Alpine ada)

**Checkpoint**: Tes siap dipakai tiap story.

---

## Phase 3: User Story 1 - Pengatur Jumlah Seragam dan Ringkas (Priority: P1) 🎯 MVP

**Goal**: Stepper 80 × 32 px, sama di semua kartu, tidak terpengaruh nama alat.

**Independent Test**: Ukur lebar/tinggi stepper di semua kartu; semuanya 80 × 32 px.

### Tests for User Story 1

- [X] T003 [US1] Tambah tes di `tests/Feature/Pages/CalculatorApplianceCardsTest.php`:
  - pembungkus stepper memuat `shrink-0`, `w-20`, `h-8`, dan grid `grid-cols-[1.75rem_1fr_1.75rem]`
  - tidak lagi memuat `h-10`
  - tombol kurang dan tambah memuat pseudo-elemen perluasan area sentuh (`before:`)
  - input angka tanpa spin (`[appearance:textfield]` dan varian `::-webkit-*-spin-button`), `p-0`, `min="0"`, `x-model.number="item.qty"`
  - perilaku Alpine utuh: `item.qty = Math.max(0, item.qty - 1); resetResult()`, `item.qty++; resetResult()`, `@input="resetResult()"`

### Implementation for User Story 1

- [X] T004 [US1] Ubah stepper di `resources/views/components/sections/calculator.blade.php` sesuai `contracts/card-layout.md`: pembungkus `grid grid-cols-[1.75rem_1fr_1.75rem] items-center shrink-0 w-20 h-8 bg-white rounded-lg border border-outline-variant overflow-hidden`; tombol `relative h-8 flex items-center justify-center text-primary text-base font-medium hover:bg-primary/5 before:absolute before:inset-y-0 before:-inset-x-[2px]`; input `w-full h-8 text-center bg-transparent border-none p-0 text-xs font-semibold focus:ring-0` dengan utilitas penghilang spin bawaan. Logika Alpine tidak diubah

**Checkpoint**: Stepper seragam.

---

## Phase 4: User Story 2 - Teks Lebih Kecil dan Ringan (Priority: P1)

**Goal**: Nama alat `text-xs font-medium`, watt `text-[11px] font-normal`.

**Independent Test**: Nama alat tidak bold dan lebih kecil; watt ≥ 11 px.

### Tests for User Story 2

- [X] T005 [US2] Tambah tes: nama alat memuat `text-xs`, `font-medium`, tidak memuat `font-bold`/`text-sm`; watt memuat `text-[11px]` dan `font-normal`, tidak memuat `text-[10px]`; ukuran watt tidak lebih besar dari nama

### Implementation for User Story 2

- [X] T006 [US2] Ubah teks di `resources/views/components/sections/calculator.blade.php`: nama `font-medium text-xs text-on-surface leading-tight` dan watt `text-[11px] font-normal text-primary/60` (sebelumnya `font-bold text-sm` dan `text-[10px]`)

---

## Phase 5: User Story 3 - Nama Panjang Tidak Mengganggu (Priority: P2)

**Goal**: Nama membungkus maksimal dua baris dan dipotong rapi; stepper tidak berubah.

**Independent Test**: Alat bernama 40 karakter; stepper tetap 80 × 32 px dan nama dua baris dengan elipsis.

### Tests for User Story 3

- [X] T007 [US3] Tambah tes: grup teks memuat `min-w-0` dan `flex-1`; nama memuat `line-clamp-2` dan `:title="item.label"`; kotak ikon memuat `shrink-0`, `w-10`, `h-10`; ikon gambar kustom dan ikon bawaan tetap dirender (kedua `x-if` ada)

### Implementation for User Story 3

- [X] T008 [US3] Ubah grup kiri di `resources/views/components/sections/calculator.blade.php`: grup `flex items-center gap-3 min-w-0 flex-1`; kotak ikon ditambah `shrink-0`; kolom teks `flex flex-col min-w-0`; nama ditambah `line-clamp-2` dan `:title="item.label"`; watt `truncate`

---

## Phase 6: User Story 4 - Rapi di Ponsel dan Desktop (Priority: P2)

**Goal**: Kartu bertinggi sama dan rapi di 320–1440 px; area sentuh nyaman.

**Independent Test**: Buka di 360 dan 1440 px; satu kolom/dua kolom, tinggi sama, tanpa gulir horizontal.

### Tests for User Story 4

- [X] T009 [US4] Tambah tes: kartu tetap `h-20` dan grid `grid-cols-1 sm:grid-cols-2`; tidak ada lebar tetap yang melebihi layar; tombol memiliki area sentuh (pseudo-elemen) pada kedua tombol

### Implementation for User Story 4

- [X] T010 [US4] (Hasil: stepper 80×32 px seragam di 9 kartu, kartu 80 px, tanpa gulir horizontal di 360 dan 1440 px. Temuan: kartu desktop hanya ±213 px karena grid kalkulator 439 px, sehingga ikon diperkecil 40→32 px dan jarak/padding dirapatkan agar area teks ±54–63 px) Verifikasi ukuran nyata di browser (`npm run build`, server dengan database sementara berisi alat bernama "TV", "Hairdryer", "Mesin Cuci 2 Tabung", "Kompor Listrik", dan satu nama 40 karakter): ukur lebar/tinggi stepper dan tinggi kartu dengan `getBoundingClientRect` di 360 dan 1440 px (SC-001, SC-003, SC-004), pastikan tidak ada gulir horizontal, lalu ambil screenshot sebelum/sesudah. Perbaiki kelas bila ada selisih

**Checkpoint**: Semua story selesai.

---

## Phase 7: Polish

- [X] T011 Jalankan `vendor/bin/pint --dirty --format agent`
- [X] T012 Jalankan `php artisan test --compact`; semua lulus (SC-007). Pastikan `CalculatorLeadTest` dan tes beranda tidak berubah hasilnya (SC-006)
- [X] T013 Jalankan `npm run build` dan simpan hasil build untuk verifikasi (kelas Tailwind baru memerlukan build)

---

## Dependencies & Execution Order

- T001 → T002 → US1 (T003–T004) → US2 (T005–T006) → US3 (T007–T008) → US4 (T009–T010) → Polish.
- Semua tugas implementasi mengubah satu blok di berkas Blade yang sama, jadi dikerjakan berurutan; tes (T003, T005, T007, T009) ditulis dulu sebelum implementasi masing-masing story.
- Tidak ada peluang paralel yang berarti (satu berkas).

## Implementation Strategy

1. **MVP**: T001–T004 (stepper seragam) sudah menyelesaikan keluhan utama.
2. Tambah teks lebih ringan (US2), penanganan nama panjang (US3), lalu verifikasi ukuran nyata (US4).
3. Polish: Pint, tes penuh, build.
4. Satu commit untuk seluruh fitur (perubahannya satu blok).

## Notes

- Watt saat ini `text-[10px]`; spec meminta ≥ 11 px sehingga ukurannya sedikit naik walau bobot dikurangi.
- Perubahan memakai kelas Tailwind baru: setelah merge, jalankan `npm run build` di setiap lingkungan, kalau tidak kartu akan tampil rusak (kasus yang sama dengan Tim Kami).
