# Quickstart: Verifikasi Manual Modul Client Logos

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-07

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `client_logos`).
- `npm run build` / `npm run dev` untuk panel Filament.

## US1 — Admin mengelola logo klien (P1) 🎯 MVP

1. Login sebagai admin, buka `/admin/client-logos`. Verifikasi daftar kosong.
2. Tambah logo: nama "PT Sinar Mas", unggah file logo (PNG), tanpa URL tautan, urutan 1, aktif. Simpan → verifikasi tersimpan & pratinjau logo tampil (format `.webp`).
3. Tambah logo kedua: nama "Tokopedia", URL tautan `https://tokopedia.com`, urutan 2, aktif. Simpan.
4. Tambah logo ketiga: urutan 3, **nonaktifkan**. Simpan.
5. Coba simpan logo tanpa nama / tanpa file → verifikasi ditolak dengan pesan error per field.
6. Coba isi URL tautan `tokopedia.com` (tanpa `https://`) → verifikasi ditolak.
7. Edit "PT Sinar Mas", ubah urutan jadi 5 → verifikasi tersimpan.
8. Hapus logo ketiga dengan konfirmasi → verifikasi hilang dari daftar.

## US2 — Logo strip di halaman Tentang Kami (P1)

1. Buka `/tentang-kami` sebagai pengunjung. Verifikasi logo strip tampil **setelah** section "Apa Kata Klien Kami" (testimoni) dan **sebelum** CTA band penutup.
2. Verifikasi hanya logo **aktif** yang tampil ("Tokopedia" lalu "PT Sinar Mas" sesuai `order` 2 lalu 5), logo nonaktif tidak muncul.
3. Klik logo "Tokopedia" → verifikasi terbuka `https://tokopedia.com` di **tab baru**.
4. Verifikasi logo "PT Sinar Mas" (tanpa URL) tidak bisa diklik (bukan tautan).
5. Di panel admin, nonaktifkan **semua** logo. Reload `/tentang-kami` → verifikasi logo strip **tidak dirender sama sekali**, dan section lain (termasuk testimoni & CTA) tetap utuh.
6. Aktifkan lagi salah satu → verifikasi strip muncul kembali pada reload berikutnya.
7. Verifikasi `/` (beranda) TIDAK menampilkan logo strip (di luar scope v1).
