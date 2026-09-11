# Quickstart: Validasi Manual SEO Management

Jalankan setelah implementasi selesai (Phase Polish), di atas data seed existing.

## 1. Fallback global (User Story 1) — tanpa override apapun

1. `php artisan serve` lalu buka `/` (Beranda) — view source, pastikan ada: `<title>`, `<meta name="description">`, `og:title`, `og:description`, `og:image`, `twitter:card=summary_large_image`, `<link rel="canonical">` mengarah ke `/`, dan `<script type="application/ld+json">` berisi `"@type":"Organization"`.
2. Ulangi untuk `/kontak`, `/karir`, `/faq` — semua field terisi (bukan blank/`null`).
3. Tempel URL beranda ke [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) — pratinjau tampil dengan judul, deskripsi, gambar (SC-001).

## 2. Override per konten (User Story 2)

1. Login admin → Produk → pilih 1 produk → buka Section "SEO" (collapsed) → isi `meta_title`, `meta_description`, upload gambar SEO → Simpan.
2. Buka halaman produk tsb di publik, view source — `<title>`/`og:title` = `meta_title` yang diisi, `og:image` = URL gambar yang diupload (bukan gambar galeri produk).
3. Ulangi untuk 1 Artikel, 1 Halaman Statis (Custom Page), 1 Proyek Portfolio.
4. Pilih 1 produk LAIN yang field SEO-nya dibiarkan kosong — buka halamannya, pastikan `og:title` = nama produk, `og:description` = potongan `short_description` (≤160 char), `og:image` = gambar pertama galeri produk (bukan kosong/rusak).
5. Ganti "OG Image" default di Brand Settings → buka lagi produk yang field SEO-nya kosong (langkah 4) → `og:image` ikut berubah tanpa edit ulang produk (SC-005).

## 3. Structured data (User Story 3)

1. Tempel URL `/faq` (dengan minimal 1 item FAQ) ke [Google Rich Results Test](https://search.google.com/test/rich-results) — `FAQPage` terdeteksi valid, 0 error.
2. Kosongkan seluruh `FaqItem` (lingkungan test/staging) → buka `/faq` lagi — pastikan TIDAK ada script `application/ld+json` bertipe `FAQPage` di source (FR-009).
3. Tempel URL 1 halaman Artikel published → `Article` terdeteksi valid.
4. Tempel URL 1 halaman Produk yang punya `price` terisi → `Product` dengan `offers.price` terdeteksi valid. Tempel URL produk TANPA `price` → `Product` valid tanpa klaim `offers` (FR-011).

## 4. Regresi

- `php artisan test --compact` — seluruh suite existing tetap hijau (tidak ada halaman publik yang berubah status/behaviour selain penambahan meta tag).
- `vendor/bin/pint --dirty --format agent` — bersih.
