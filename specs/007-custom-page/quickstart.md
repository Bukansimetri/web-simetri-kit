# Quickstart: Verifikasi Manual Custom Page

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-07

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `custom_pages`).

## US1 — Admin membuat & mengedit halaman statis (P1) 🎯 MVP

1. Login sebagai admin, buka `/admin/custom-pages`. Verifikasi daftar kosong (belum ada seed untuk modul ini).
2. Tambah halaman baru: judul "Kebijakan Privasi", isi lewat rich text editor (coba bold/heading/link). Kosongkan slug. Simpan.
3. Verifikasi slug ter-generate otomatis jadi `kebijakan-privasi`. Buka `/halaman/kebijakan-privasi`, verifikasi isi tampil sesuai format yang ditulis.
4. Edit halaman tsb, ubah judul jadi "Kebijakan Privasi SUOER", simpan. Reload `/halaman/kebijakan-privasi`, verifikasi judul baru tampil.
5. Coba buat halaman baru dengan slug yang sama persis (`kebijakan-privasi`), verifikasi ditolak.
6. Coba simpan halaman tanpa mengisi judul/isi, verifikasi ditolak dengan pesan error per field.
7. Hapus halaman "Kebijakan Privasi SUOER", verifikasi `/halaman/kebijakan-privasi` mengembalikan 404.

## US2 — Link footer Kebijakan Privasi & Syarat Ketentuan (P1)

1. Buka halaman publik mana pun (mis. beranda `/`), verifikasi link "Kebijakan Privasi" dan "Syarat & Ketentuan" di footer mengarah ke `/halaman/kebijakan-privasi` dan `/halaman/syarat-ketentuan` (bukan lagi ke `/tentang-kami`).
2. **Sebelum** membuat kedua halaman: klik link "Kebijakan Privasi" di footer, verifikasi mengembalikan 404 (halaman belum dibuat admin).
3. Sebagai admin, buat dua halaman baru dengan slug PERSIS `kebijakan-privasi` dan `syarat-ketentuan` (lihat contracts/admin-panel-surface.md §2 untuk slug konvensi).
4. Klik lagi link "Kebijakan Privasi"/"Syarat & Ketentuan" di footer, verifikasi sekarang mendarat di halaman dengan konten yang sesuai.
5. Buka `/tentang-kami`, verifikasi halaman ini TIDAK berubah sama sekali (masih desain khusus hero/visi-misi yang sudah ada, bukan Custom Page).
