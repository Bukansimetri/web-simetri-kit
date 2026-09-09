# Quickstart: Verifikasi Manual Modul Banner Management

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-10

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `banners`).
- `npm run build` / `npm run dev` (panel Filament + Alpine carousel di beranda).

## US1 — Admin mengelola banner (P1) 🎯 MVP

1. Login sebagai admin, buka `/admin/banners`. Verifikasi daftar kosong.
2. Tambah banner A: judul "Promo Kemerdekaan", unggah gambar (salah satu > 1600px lebar), teks alt "Diskon 17% pemasangan panel surya", URL `https://contoh.com/promo`, tanpa periode, urutan 1, aktif. Simpan.
3. Verifikasi gambar tersimpan di `storage/app/public/banners/` berformat `.webp` dan gambar > 1600px kini lebarnya 1600px. Kolom status menunjukkan "Tayang".
4. Tambah banner B: periode mulai **besok**, selesai +10 hari, urutan 2, aktif. Simpan → kolom status "Terjadwal".
5. Tambah banner C: periode mulai −10 hari, selesai **kemarin**, aktif. Simpan → kolom status "Kedaluwarsa".
6. Tambah banner D: aktif, tanpa periode, lalu **nonaktifkan**. Simpan → kolom status "Nonaktif".
7. Coba simpan banner tanpa judul / tanpa gambar / tanpa teks alt → verifikasi ditolak dengan pesan per field.
8. Coba isi URL `contoh.com` (tanpa skema) → verifikasi ditolak.
9. Coba isi periode selesai lebih awal dari mulai → verifikasi ditolak.
10. Edit banner B, geser tanggal mulai ke **kemarin** → simpan, verifikasi status berubah jadi "Tayang".
11. Hapus banner C dengan konfirmasi → verifikasi terhapus.

## US2 — Banner sebagai hero beranda (P1)

1. Dengan banner A & B (setelah langkah 10) keduanya "Tayang", buka `/`. Verifikasi di posisi hero muncul **carousel** berisi kedua banner, bukan hero statis; giliran sesuai urutan (A lalu B); ada kontrol navigasi (dots / prev-next) dan auto-rotate.
2. Klik banner A → verifikasi diarahkan ke `https://contoh.com/promo` (tab yang sama).
3. Nonaktifkan banner B (sisakan hanya A tayang). Reload `/` → verifikasi banner A tampil **tunggal** di posisi hero tanpa kontrol carousel.
4. Nonaktifkan/lewatkan periode **semua** banner. Reload `/` → verifikasi posisi hero kembali menampilkan **Hero statis** yang lama ("Nyalakan rumah Anda dengan energi matahari"), tanpa ruang kosong; section beranda lain (kalkulator, produk, dll) tetap utuh.
5. Verifikasi halaman lain (`/produk`, `/tentang-kami`) TIDAK menampilkan banner.
6. Verifikasi banner "Terjadwal" dan "Kedaluwarsa" tidak pernah tampil di beranda.
