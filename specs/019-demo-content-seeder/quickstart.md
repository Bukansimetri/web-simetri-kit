# Quickstart: Demo Content Seeder

**Feature**: 019-demo-content-seeder

## Untuk sales/ops (menyiapkan demo ke calon klien)

```bash
php artisan migrate
php artisan demo:seed
```

Buka halaman publik Layanan, Tentang Kami (Tim), Testimoni, dan Portfolio — semuanya sudah berisi konten contoh yang realistis.

## Setelah demo selesai, sebelum instalasi diserahkan sebagai produksi klien

```bash
php artisan demo:clean
```

Verifikasi keempat modul kembali kosong (kecuali entri yang sudah ditambahkan admin secara manual, yang tetap dipertahankan).

## Menjalankan ulang dengan aman

```bash
php artisan demo:seed
```

Aman dijalankan berkali-kali — entitas yang sudah pernah di-seed dilewati, tidak menghasilkan duplikat.

## Verifikasi manual (developer, selama implementasi)

```bash
php artisan test --compact tests/Feature/Console/DemoSeedCommandTest.php tests/Feature/Console/DemoCleanCommandTest.php
```

1. Pada database kosong, jalankan `php artisan demo:seed` — konfirmasi kelima entitas (kategori portfolio, layanan, tim, testimoni, proyek portfolio) terisi dan tabel `demo_seed_records` mencatat setiap baris.
2. Jalankan `php artisan demo:seed` lagi — konfirmasi jumlah baris tidak bertambah (tidak duplikat).
3. Tambahkan satu `TeamMember` manual lewat Tinker/admin panel (bukan lewat `demo:seed`).
4. Jalankan `php artisan demo:clean` — konfirmasi seluruh data demo terhapus, TAPI `TeamMember` manual dari langkah 3 tetap ada.
5. Konfirmasi `php artisan migrate --seed` (dari awal, database kosong) TIDAK menghasilkan satu pun baris di kelima entitas tsb.
