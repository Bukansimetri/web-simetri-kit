# Research: Perapian Kartu Peralatan di Kalkulator

Tidak ada `NEEDS CLARIFICATION`.

## R1. Penyebab ukuran stepper berbeda

- **Temuan**: Struktur kartu saat ini:
  - Kartu: `flex items-center justify-between … h-20`.
  - Grup kiri: `flex items-center gap-3` (ikon `w-10` + nama/watt), tanpa `min-w-0`/`flex-1`.
  - Stepper: `flex items-center … h-10` dengan anak `w-7`, `<input class="w-8">`, `w-7`, tanpa `shrink-0` dan tanpa lebar tetap.
- **Akibat**: Flex mengalokasikan sisa ruang berdasarkan ukuran isi. Nama panjang (`Mesin Cuci 2 Tabung`) memperbesar grup kiri sehingga stepper tertekan (flex-shrink 1). Input angka dan tombol bisa menyusut, sehingga lebar stepper berbeda antar kartu. Itu cocok dengan screenshot: stepper kartu "TV" jauh lebih lebar daripada "Hairdryer".
- **Decision**: Kunci ukuran stepper (`shrink-0`, lebar dan tinggi tetap) dan biarkan nama yang menyesuaikan lewat `min-w-0 flex-1` pada grup kiri.

## R2. Ukuran target

- **Decision**: Stepper `w-20 h-8` (80 × 32 px, tepat di batas FR-002) berupa grid tiga kolom `grid-cols-[1.75rem_1fr_1.75rem]` (tombol 28 px, angka 24 px, tombol 28 px). Input angka tanpa tombol spin bawaan (`[appearance:textfield]`, `[&::-webkit-inner-spin-button]:appearance-none`, `[&::-webkit-outer-spin-button]:appearance-none`) dan `p-0` agar lebar angka pasti.
- **Area sentuh (FR-007)**: tombol 28 px lebar diperluas ke 32 px lewat pseudo-elemen (`relative before:absolute before:inset-y-0` dan `before:left-0 before:-right-1` untuk tombol kurang, `before:right-0 before:-left-1` untuk tombol tambah; hanya ke arah dalam karena sisi luar terpotong `overflow-hidden`), sehingga area yang bisa disentuh 32 × 32 px tanpa menambah ukuran tampilan. Tinggi tombol sudah 32 px.
- **Rationale**: Memenuhi permintaan "diperkecil dan seragam" (88 × 40 px → 80 × 32 px, turun ±27% luasnya, memenuhi SC-002) tanpa mengorbankan kenyamanan sentuh.
- **Alternatives**: Stepper 96 × 40 (terlalu besar); tombol 24 × 24 (tidak nyaman disentuh); tombol 32 px sungguhan dengan stepper 88 px (melampaui FR-002).

## R3. Tipografi

- **Decision**: Nama alat `text-xs font-medium text-on-surface leading-tight line-clamp-2`; watt `text-[11px] font-normal text-primary/60`; angka stepper `text-xs font-semibold` (angka tetap mudah terbaca, tapi tidak lebih tebal dari yang diperlukan). Tombol `+`/`−` `text-base font-medium`.
- **Rationale**: Menurunkan satu tingkat ukuran (`text-sm` → `text-xs`) dan bobot (`bold` → `medium`) sesuai permintaan, watt tidak lebih kecil dari 11 px (FR-004).
- **Catatan**: Aturan global spec 031 hanya memaksa bobot 700 untuk `h1`–`h6` dan `.btn-fill`; `<span>` nama alat tidak terkena.

## R4. Nama panjang

- **Decision**: `line-clamp-2` pada nama, `title` berisi nama lengkap (`:title="item.label"`), dan grup kiri `min-w-0 flex-1` agar teks dibungkus alih-alih mendorong stepper.
- **Alternatives**: `truncate` satu baris (nama seperti "Mesin Cuci 2 Tabung" terpotong tanpa perlu).

## R5. Ikon dan tinggi kartu

- **Decision**: Kotak ikon tetap `w-10 h-10` dengan `shrink-0`; kartu tetap `h-20` agar semua kartu bertinggi sama. Tidak ada perubahan lain.

## R6. Pengujian

- Perubahan murni gaya, jadi tes Feature merender `/` dan memeriksa potongan template Alpine di HTML:
  - Kelas stepper berisi `shrink-0` dan lebar/tinggi tetap.
  - Nama tidak berisi `font-bold`, memuat `text-xs`, `font-medium`, `line-clamp-2`.
  - Watt memuat `text-[11px]`.
  - Tombol memiliki area sentuh 32 px.
  - Atribut perilaku Alpine tetap (`@click`, `x-model.number`, `resetResult()`, `Math.max(0,`).
- Pengukuran nyata (SC-001, SC-003, SC-004, SC-005) dilakukan di browser headless dengan alat contoh bernama pendek dan panjang, di lebar 360 dan 1440 px.
