# Quickstart: Validasi Manual Sitemap & Robots

Jalankan setelah implementasi selesai (Phase Polish), di atas data seed existing.

## 1. Sitemap mencakup semua halaman yang bisa diakses (User Story 1)

1. Buka `/sitemap.xml` di browser — pastikan tampil sebagai XML valid (bukan error/blank), `Content-Type` response = `application/xml`.
2. Pastikan 7 halaman statis ada: `/`, `/tentang-kami`, `/kontak`, `/faq`, `/artikel`, `/produk`, `/portfolio`.
3. Pastikan setiap Produk, Artikel published, Halaman Statis, dan Proyek Portfolio aktif yang ada di database masing-masing muncul sebagai satu `<url>`.
4. Salin 5-10 `<loc>` acak dari daftar (campuran tiap jenis), buka satu per satu di tab baru — semua MUST tampil sukses (SC-001).

## 2. Konten tidak-published tidak muncul

1. Set satu artikel jadi draft (`published_at` null) atau terjadwal masa depan → buka ulang `/sitemap.xml` → URL artikel itu MUST hilang.
2. Nonaktifkan satu Proyek Portfolio (`is_active` false) → buka ulang `/sitemap.xml` → URL proyek itu MUST hilang.
3. Publikasikan artikel yang tadi di-draft → buka ulang `/sitemap.xml` → URL muncul kembali TANPA proses build/deploy tambahan (SC-002/SC-003).

## 3. Toggle modul Karir

1. Di Brand Settings admin, matikan "Modul Karir Aktif" → buka `/sitemap.xml` → `/karir` MUST tidak ada.
2. Aktifkan kembali → buka ulang → `/karir` muncul lagi.

## 4. robots.txt menunjuk ke sitemap

1. Buka `/robots.txt` — pastikan masih ada `User-agent: *` dan `Disallow:` kosong (tidak berubah dari sebelumnya).
2. Pastikan ada baris `Sitemap: <url absolut ke /sitemap.xml>`, dan URL itu memakai domain yang sama dengan yang sedang diakses (bukan hardcoded domain lain).
3. Klik/buka URL di baris `Sitemap:` tsb → harus mengarah tepat ke halaman `/sitemap.xml` yang sudah divalidasi di langkah 1.

## 5. Regresi

- `php artisan test --compact` — seluruh suite existing tetap hijau.
- `vendor/bin/pint --dirty --format agent` — bersih.
- Pastikan tidak ada lagi file `public/robots.txt` statis (harus sudah dihapus, digantikan route).
