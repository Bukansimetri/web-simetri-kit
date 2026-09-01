# Quickstart: Verifikasi Manual Theme & Branding System

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-01

Langkah verifikasi manual untuk ketiga user story setelah implementasi selesai (mengikuti pola `quickstart.md` di `001-epic2-cleanup`).

## Prasyarat

- `php artisan migrate --seed` sudah dijalankan (menjalankan `ProductSeeder`, `ArticleSeeder`, `JobOpeningSeeder`, `FaqItemSeeder`).
- `npm run build` (atau `npm run dev`) sudah dijalankan supaya asset Tailwind/Alpine/font ter-compile.

## US1 — Home & Produk dengan desain final SUOER (P1)

1. Buka `/` tanpa login. Verifikasi: hero, section "Kenapa Pilih SUOER", "Cara Kerja", "Produk Kami", dan CTA tampil sesuai mockup `home_suoer_html_calculator_results`.
2. Isi kalkulator estimasi (mode "Per Tagihan" dan mode "Per Alat" bergantian). Verifikasi hasil estimasi & grafik berubah tanpa reload halaman, dan tidak ada request network baru saat interaksi (cek tab Network browser).
3. Coba input tidak wajar (0 atau negatif) di kalkulator. Verifikasi muncul pesan validasi, bukan hasil NaN/rusak.
4. Buka `/produk`. Verifikasi daftar produk tampil sesuai seed data. Klik salah satu produk, verifikasi halaman detail (`/produk/{slug}`) tampil sesuai mockup `produk_detail_suoer_header_aligned` (harga, spesifikasi, related products).

## US2 — Halaman pendukung company profile (P2)

1. Buka `/tentang-kami`, `/karir`, `/artikel`, `/faq` satu per satu tanpa login. Verifikasi struktur & konten sesuai mockup masing-masing.
2. Buka `/kontak`. Kosongkan field wajib dan submit — verifikasi pesan validasi muncul tanpa reload/network call.
3. Isi form Kontak dengan data valid dan submit — verifikasi muncul konfirmasi yang wajar ke pengguna (mis. pesan "terkirim"), tanpa error teknis, dan tanpa request POST sungguhan ke server (cek Network tab).
4. Hapus/nonaktifkan seluruh data seed `Article` sementara (mis. via tinker/db) dan reload `/artikel` — verifikasi empty state wajar tampil, bukan error 500. Kembalikan data seed setelah selesai.

## US3 — Theme Settings admin (P3)

1. Login sebagai admin, buka halaman Brand/Theme Settings (`/admin/...`). Verifikasi field warna sekunder, font heading, font body, dan OG image sudah terisi nilai default Luminous Azure (`#3a5f94`, Manrope, Be Vietnam Pro).
2. Ubah warna sekunder ke warna lain dan font heading/body ke pilihan lain di dropdown, simpan. Buka beberapa halaman publik (mis. `/`, `/produk`), verifikasi warna & font berubah konsisten di semua halaman tanpa deploy ulang.
3. Upload OG image baru, simpan. `curl -s http://<host>/ | grep 'og:image'` (atau view-source browser) — verifikasi meta tag menunjuk ke gambar baru.
4. Kosongkan kembali warna sekunder (reset field), simpan. Verifikasi halaman publik kembali ke warna default Luminous Azure, bukan tampilan rusak.
5. Coba submit font dengan nilai di luar dropdown kurasi (via devtools/manipulasi request jika perlu). Verifikasi sistem menolak dengan pesan error, tidak tersimpan.
