# Quickstart: Verifikasi Manual Modul Portfolio

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-07

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `portfolio_categories`, `portfolio_projects`).
- `npm run build` / `npm run dev` untuk panel Filament.

## US1 — Admin mengelola kategori portfolio (P2)

1. Login sebagai admin, buka `/admin/portfolio-categories`. Verifikasi daftar kosong.
2. Tambah 3 kategori: "Instalasi Atap" (urutan 1), "PLTS Industri" (urutan 2), "Pompa Air Surya" (urutan 3). Verifikasi slug ter-generate (`instalasi-atap`, dst).
3. Coba tambah kategori bernama "Instalasi Atap" lagi → verifikasi ditolak (nama unik).
4. Ubah nama "Pompa Air Surya" → verifikasi tersimpan.
5. Hapus "Pompa Air Surya" (belum dipakai) → verifikasi terhapus.

## US2 — Admin mengelola proyek portfolio (P1) 🎯 MVP

1. Buka `/admin/portfolio-projects`. Tambah proyek: judul "Atap Rumah 5 kWp Bandung", kategori "Instalasi Atap", deskripsi via rich text, unggah 3 gambar (salah satunya > 1200px lebar), kosongkan slug/URL/klien/tanggal, urutan 1, aktif. Simpan.
2. Verifikasi slug = `atap-rumah-5-kwp-bandung`. Cek file tersimpan di `storage/app/public/portfolio/` berformat `.webp` dan gambar yang tadinya > 1200px kini lebarnya 1200px.
3. Tambah proyek kedua: kategori "PLTS Industri", isi URL proyek `https://contoh.com/proyek`, nama klien "PT ABC", tanggal selesai. Aktif.
4. Tambah proyek ketiga: kategori "PLTS Industri", **nonaktifkan**.
5. Coba simpan proyek tanpa judul / tanpa kategori / tanpa deskripsi / tanpa gambar → verifikasi ditolak dengan pesan per field.
6. Coba isi URL proyek `contoh.com` (tanpa skema) → verifikasi ditolak.
7. Edit proyek pertama, ubah urutan gambar (drag), simpan → verifikasi urutan baru tersimpan.
8. Hapus proyek ketiga dengan konfirmasi → verifikasi terhapus.

## US3 — Halaman publik portfolio (P1)

1. Buka `/portfolio`. Verifikasi hanya proyek **aktif** tampil (proyek 1 & 2), terurut sesuai `order`; tiap kartu menampilkan sampul + judul + nama kategori.
2. Klik chip filter "PLTS Industri" → URL jadi `/portfolio?kategori=plts-industri`, hanya proyek 2 yang tampil. Salin URL, buka di tab baru → hasil sama.
3. Klik chip "Instalasi Atap" → hanya proyek 1. Klik "Semua" → kedua proyek kembali.
4. Buka `/portfolio?kategori=tidak-ada` → verifikasi tampil semua proyek aktif (filter diabaikan), tanpa error.
5. Klik proyek 2 → `/portfolio/{slug}`. Verifikasi galeri semua gambar tampil sesuai urutan, deskripsi tampil, nama klien + tanggal selesai + tombol "Kunjungi proyek" tampil; tautan membuka di tab baru.
6. Buka proyek 1 → verifikasi TIDAK ada label kosong untuk klien/tanggal/tautan (karena tidak diisi).
7. Buka `/portfolio/{slug-proyek-nonaktif}` dan `/portfolio/slug-ngawur` → verifikasi keduanya 404.
8. Nonaktifkan semua proyek. Buka `/portfolio` → verifikasi HTTP 200 dengan pesan "Belum ada proyek" (bukan 404).
