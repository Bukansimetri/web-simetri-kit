# Quickstart: Verifikasi Manual Modul Team Members

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-08

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `team_members`).
- `npm run build` / `npm run dev` untuk panel Filament.

## US1 — Admin mengelola anggota tim (P1) 🎯 MVP

1. Login sebagai admin, buka `/admin/team-members`. Verifikasi daftar kosong.
2. Tambah anggota: nama "Andi Wijaya", jabatan "CEO & Founder", unggah foto (salah satunya > 800px lebar), bio 1–2 kalimat, LinkedIn `https://linkedin.com/in/andiwijaya`, urutan 1, aktif. Simpan.
3. Verifikasi foto tersimpan di `storage/app/public/team/` berformat `.webp` dan foto yang tadinya > 800px kini lebarnya 800px.
4. Tambah anggota kedua: jabatan "CTO", **kosongkan** LinkedIn, urutan 2, aktif. Simpan → verifikasi tersimpan tanpa error.
5. Tambah anggota ketiga: urutan 3, **nonaktifkan**. Simpan.
6. Coba simpan anggota tanpa nama / tanpa jabatan / tanpa foto / tanpa bio → verifikasi ditolak dengan pesan per field.
7. Coba isi LinkedIn `linkedin.com/in/x` (tanpa skema) → verifikasi ditolak.
8. Edit "Andi Wijaya", ubah jabatan & urutan, simpan → verifikasi tersimpan.
9. Hapus anggota ketiga dengan konfirmasi → verifikasi terhapus.

## US2 — Section "Tim Kami" di halaman Tentang Kami (P1)

1. Buka `/tentang-kami` sebagai pengunjung. Verifikasi section "Tim Kami" tampil **setelah** blok "Nilai-Nilai Kami" dan **sebelum** section "Apa Kata Klien Kami" (testimoni).
2. Verifikasi hanya anggota **aktif** yang tampil ("Andi Wijaya" & "CTO"), dalam urutan sesuai `order`. Anggota nonaktif tidak muncul.
3. Verifikasi kartu "Andi Wijaya" menampilkan foto + nama + jabatan + bio + ikon LinkedIn; klik ikon → terbuka profil di tab baru.
4. Verifikasi kartu anggota kedua (tanpa LinkedIn) tampil rapi tanpa ikon/area LinkedIn kosong.
5. Di panel admin, nonaktifkan **semua** anggota. Reload `/tentang-kami` → verifikasi section "Tim Kami" **tidak dirender sama sekali**, dan section lain (nilai, testimoni, logo klien, CTA) tetap utuh.
6. Aktifkan lagi salah satu → verifikasi section muncul kembali pada reload berikutnya.
7. Verifikasi `/` (beranda) TIDAK menampilkan section tim (di luar scope v1).
