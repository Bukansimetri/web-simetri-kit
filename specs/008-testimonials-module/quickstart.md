# Quickstart: Verifikasi Manual Modul Testimonials

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-07

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `testimonials`).
- `npm run build` / `npm run dev` untuk panel Filament.

## US1 — Admin mengelola testimoni (P1) 🎯 MVP

1. Login sebagai admin, buka `/admin/testimonials`. Verifikasi daftar kosong.
2. Tambah testimoni: nama "Budi Santoso", atribusi "Pemilik Rumah, Bandung", isi testimoni beberapa kalimat, rating 5, tanpa foto, urutan 1. Simpan → verifikasi tersimpan & muncul di daftar.
3. Tambah testimoni kedua: nama "Sari Dewi", atribusi dikosongkan, rating 4, unggah foto, urutan 2, aktif. Simpan → verifikasi foto tampil sebagai pratinjau (format `.webp`).
4. Tambah testimoni ketiga: rating 3, urutan 3, **nonaktifkan** (`is_active` off). Simpan.
5. Coba simpan testimoni tanpa nama / tanpa isi / tanpa rating → verifikasi ditolak dengan pesan error per field.
6. Coba set rating ke nilai di luar 1–5 (mis. lewat manipulasi) → verifikasi ditolak.
7. Edit testimoni "Budi Santoso", ubah isi & urutan → verifikasi tersimpan.
8. Hapus testimoni ketiga dengan konfirmasi → verifikasi hilang dari daftar.

## US2 — Section testimoni di halaman Tentang Kami (P1)

1. Buka `/tentang-kami` sebagai pengunjung. Verifikasi section "Testimoni" tampil **setelah** blok "Nilai-Nilai Kami" dan **sebelum** CTA band penutup.
2. Verifikasi hanya testimoni **aktif** yang tampil ("Budi Santoso" & "Sari Dewi"), dalam urutan sesuai nilai `order` (1 lalu 2). Testimoni nonaktif tidak muncul.
3. Verifikasi "Budi Santoso" (tanpa foto) tampil dengan placeholder inisial "B", bukan gambar rusak. "Sari Dewi" tampil dengan fotonya.
4. Verifikasi rating tampil sebagai bintang: 5 bintang terisi untuk Budi, 4 terisi + 1 kosong untuk Sari.
5. Di panel admin, nonaktifkan **semua** testimoni. Reload `/tentang-kami` → verifikasi section testimoni **tidak dirender sama sekali** (tidak ada heading/area kosong), dan section lain (hero, visi, misi, nilai, CTA) tetap utuh.
6. Aktifkan lagi salah satu → verifikasi section muncul kembali pada reload berikutnya.
7. Verifikasi `/` (beranda) TIDAK menampilkan testimoni (di luar scope v1).
