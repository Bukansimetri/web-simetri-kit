# Contract: Tata Letak Kartu Peralatan

Berlaku untuk setiap kartu di blok "Pilih jumlah peralatan listrik di rumah Anda" pada kalkulator beranda.

| Elemen | Ketentuan |
|---|---|
| Kartu | tinggi tetap `h-20` (80 px), padding `p-2.5`, jarak `gap-2`; dua kolom ≥ 640 px, satu kolom di bawahnya |
| Kotak ikon | 32 × 32 px, `shrink-0`; ikon bawaan 20 px atau gambar kustom `w-5 h-5 object-contain` (diperkecil dari 40 px agar teks punya ±63 px di kartu 213 px) |
| Grup teks (kiri) | `min-w-0 flex-1`; nama dan watt membungkus atau dipotong, tidak pernah mendorong stepper |
| Nama alat | `text-xs font-medium leading-tight`, maksimal 2 baris (`line-clamp-2`), atribut `title` = nama lengkap |
| Keterangan watt | `text-[11px] font-normal`, warna samar, satu baris |
| Stepper | 80 × 32 px, `shrink-0`, grid 28 / 24 / 28 px, selalu di tepi kanan kartu |
| Tombol kurang/tambah | tampil 28 × 32 px, area sentuh 32 × 32 px, `text-base font-medium` |
| Angka | `text-xs font-semibold`, tengah, tanpa tombol spin, `min=0`, `x-model.number` tetap |

Perilaku Alpine tidak berubah: `item.qty = Math.max(0, item.qty - 1); resetResult()`, `item.qty++; resetResult()`, dan `@input="resetResult()"`.
