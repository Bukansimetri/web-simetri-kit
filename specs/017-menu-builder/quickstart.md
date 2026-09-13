# Quickstart: Menu Builder

**Feature**: 017-menu-builder

## Untuk admin/editor (setelah fitur ini live)

1. Buka admin panel → menu **Menu Builder** (grup navigasi/konten).
2. Untuk menambah item menu baru:
   - Klik **New Menu Item**.
   - Isi **Label** (teks yang tampil ke pengunjung).
   - Pilih **Lokasi** (mis. "Navbar Utama" atau "Footer").
   - Pilih **Jenis Tautan**: Halaman Internal / URL Eksternal / Tanpa Tautan.
     - Jika Halaman Internal: pilih jenis konten lalu pilih record spesifiknya dari dropdown.
     - Jika URL Eksternal: isi URL lengkap, opsional centang "Buka di tab baru".
   - Simpan.
3. Untuk mengubah urutan: buka tabel item menu pada lokasi yang sama, seret (drag) baris menggunakan handle di kiri baris ke posisi yang diinginkan.
4. Untuk sub-menu: buat item baru, lalu pilih **Induk Menu** ke item lain yang sudah ada (item tersebut harus berada di lokasi yang sama, dan tidak boleh sudah menjadi sub-item dari item lain).
5. Untuk menyembunyikan sementara tanpa menghapus: nonaktifkan toggle **Aktif** pada item tersebut.
6. Untuk menambah lokasi menu baru (mis. "Menu Mobile" khusus): buka **Menu Locations** → **New**, isi nama, slug otomatis terisi.

Perubahan langsung terlihat di halaman publik setelah disimpan (tidak perlu deploy atau tunggu cache).

## Untuk developer (verifikasi manual selama implementasi)

```bash
php artisan migrate
php artisan db:seed --class=MenuSeeder
php artisan test --compact tests/Feature/Admin/MenuItemResourceTest.php
php artisan test --compact tests/Feature/Public/MenuRenderingTest.php
```

Verifikasi visual:
1. `composer run dev` (atau `npm run dev` + `php artisan serve` sesuai setup lokal).
2. Login admin panel, tambah item menu untuk lokasi `Navbar Utama` mengarah ke `CustomPage` yang sudah ada.
3. Buka halaman publik manapun, konfirmasi item baru muncul di navbar dan tautannya benar.
4. Ubah slug `CustomPage` tersebut di admin, refresh halaman publik, konfirmasi tautan menu otomatis mengikuti slug baru (FR-008).
5. Hapus `CustomPage` tersebut, refresh halaman publik, konfirmasi navbar tetap render tanpa error dan item menu tampil tanpa tautan aktif (FR-011); cek badge peringatan di admin panel.
