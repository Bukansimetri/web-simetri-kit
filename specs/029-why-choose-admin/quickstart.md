# Quickstart: Kelola Section Konten & CTA dari Panel Admin

**Feature**: `029-why-choose-admin`

## 0. Sebelum mengubah Blade (wajib, sekali)

Ambil HTML lama sebagai fixture kesetaraan (contracts/public-render.md §4). Lakukan ini **sebelum** menyentuh view:

```bash
CAPTURE_LEGACY_FIXTURES=1 php artisan test --compact --filter=CaptureLegacyPageContentFixturesTest   # menulis tests/Fixtures/legacy-page-content/*.html; tanpa env ini test ter-skip
```

Setelah fixture ter-commit, test penangkap dihapus atau di-skip. Simpan juga tangkapan layar setiap halaman terdampak (mobile 390px & desktop 1440px): `/`, `/produk`, `/produk/{slug}`, `/artikel`, `/artikel/{slug}`, `/tentang-kami`, `/faq`, `/karir`.

## 1. Setup lokal

```bash
php artisan migrate            # membuat 3 tabel + menanam konten bawaan (migrasi data → PageContentInstaller)
php artisan db:seed --class=PageContentSeeder   # opsional, aman diulang (tidak menduplikasi)
npm run build                  # hanya jika ada perubahan class Tailwind (seharusnya tidak ada)
```

## 2. Verifikasi tampilan tidak berubah

1. Buka kedelapan halaman di atas. Bandingkan dengan tangkapan layar langkah 0. **Harus identik.**
2. Jalankan test kesetaraan:

```bash
php artisan test --compact --filter='LegacyMarkupEquivalenceTest|DefaultPageContentTest'
```

## 3. Skenario admin (login `/admin`, grup **Konten Halaman**)

| # | Langkah | Hasil yang diharapkan |
|---|---|---|
| 1 | **Beranda – Mengapa Beralih** → edit "Efisien & Terjangkau", ubah judul | Beranda menampilkan judul baru, kartu lain tetap |
| 2 | Tambah kartu ke-4 dengan Aktif = on | Ditolak: "maksimal 3 item aktif" |
| 3 | Simpan kartu ke-4 sebagai nonaktif, lalu toggle aktif di tabel | Ditolak dengan pesan yang sama |
| 4 | Nonaktifkan "Ramah Lingkungan", aktifkan kartu ke-4, seret ke atas | Beranda menampilkan urutan baru |
| 5 | Tonjolkan "Efisien & Terjangkau" | "Garansi Panjang" otomatis tidak ditonjolkan, kartu biru pindah |
| 6 | Header action **Ubah Judul Section**, tulis judul 2 baris | Beranda memotong baris di tempat yang sama |
| 7 | **Beranda – Cara Kerja** → seret "Inverter" ke posisi 1 | Nomor `01` dan kemiringan/posisi posisi-1 kini dipakai "Inverter" |
| 8 | **Karir – Proses Rekrutmen** → nonaktifkan semua langkah | Seluruh kotak "Proses Rekrutmen" hilang dari `/karir` |
| 9 | **Karir – Mengapa Bergabung** → form tidak punya "Tonjolkan" | Sesuai desain |
| 10 | **CTA** → edit "FAQ – CTA" | Hanya `/faq` yang berubah. Tidak ada tombol Tambah/Hapus |
| 11 | **CTA** → "Detail Produk" body pakai `{produk}` | Nama produk (huruf kecil) muncul di setiap detail produk |
| 12 | Isi judul item dengan `<script>alert(1)</script>` | Tampil sebagai teks, tidak dijalankan |

## 4. Test otomatis

```bash
php artisan test --compact tests/Feature/Admin/SectionItemResourcesTest.php
php artisan test --compact tests/Feature/Admin/CallToActionResourceTest.php
php artisan test --compact tests/Feature/Pages/PageContentRenderTest.php
php artisan test --compact tests/Feature/Pages/CallToActionRenderTest.php
php artisan test --compact tests/Feature/Pages/LegacyMarkupEquivalenceTest.php
php artisan test --compact tests/Unit/DefaultPageContentTest.php
php artisan test --compact tests/Feature/Database/PageContentSeederTest.php
vendor/bin/pint --dirty --format agent
```

## 5. Deploy

1. **Sebelum deploy**: pastikan **Pengaturan Situs → Nama Situs = "SUOER"** di production. Jika Nama Situs kosong, pastikan `APP_NAME=SUOER` di `.env` production. Nama ini dipakai **sekali** saat migrasi untuk mengisi teks bawaan (judul "Mengapa Beralih Bersama …?", CTA Tentang Kami, tombol CTA Produk). Setelah itu teks tidak lagi bergantung pada Nama Situs dan bisa diubah admin (research R11).
2. `php artisan migrate --force` (docs/deployment.md) menanam konten bawaan melalui migrasi data (`PageContentInstaller`, bukan seeder). Migrasi aman di instalasi yang sudah punya data: section yang sudah punya judul dan CTA yang sudah ada tidak disentuh.
