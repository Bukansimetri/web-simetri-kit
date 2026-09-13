# Phase 0 Research: Sitemap & Robots Otomatis

## 1. Generate sitemap.xml: package vs hand-rolled

**Decision**: Hand-rolled — satu Blade view XML (`resources/views/sitemap.blade.php`) yang di-loop dari data yang sudah tersedia di masing-masing controller/model, dirender lewat satu controller baru. Tidak menambah dependency.

**Rationale**: Cakupan sitemap fitur ini kecil dan tetap (≤11 sumber: 6-7 halaman statis + 4 koleksi konten) — bukan kebutuhan generik "sitemap builder" dengan caching/queue/multi-sitemap-index yang jadi alasan utama paket seperti `spatie/laravel-sitemap` ada. Blade `{{ }}` sudah otomatis meng-escape karakter spesial XML (`&`, `<`, `>` → entity HTML/XML yang sama) lewat `htmlspecialchars`, jadi edge case "slug mengandung `&`" (spec.md § Edge Cases) sudah tertangani gratis tanpa logic tambahan. Konsisten dengan preseden AMC-223 (research.md-nya menolak `artesaos/seotools` dan `spatie/schema-org` dengan alasan serupa — surface area package tidak sepadan dengan kebutuhan yang sederhana), dan menjaga daftar dependency tetap minim untuk audit lisensi pra-rilis v1.0 (AMC-235).

**Alternatives considered**: `spatie/laravel-sitemap` — paket resmi Spatie (masuk daftar preferensi Principle V), matang dan dipakai luas, tapi menambah API fluent (`Sitemap::create()->add(Url::create(...))`) yang tidak memberi nilai lebih dibanding loop Blade langsung untuk kasus se-simpel ini; disimpan sebagai opsi bila kebutuhan berkembang (mis. sitemap index terpisah per jenis konten, image sitemap) di luar cakupan fitur ini.

## 2. `robots.txt` statis → dinamis

**Decision**: Hapus `public/robots.txt` (file statis), ganti dengan route `GET /robots.txt` yang dilayani controller/view baru — isi sama persis (`User-agent: *` / `Disallow:` kosong = izinkan semua, FR-011) ditambah satu baris `Sitemap: {url mutlak ke /sitemap.xml}`.

**Rationale**: Selama `public/robots.txt` masih ada sebagai file fisik, web server (Nginx/Apache/`php artisan serve`) akan selalu menyajikannya langsung dan permintaan TIDAK PERNAH sampai ke router Laravel (konfigurasi `try_files` standar Laravel hanya fallback ke `index.php` bila file tidak ditemukan) — jadi route baru tidak akan pernah dieksekusi kalau file lama masih ada. Menghapus file statis adalah satu-satunya cara membuat `robots.txt` benar-benar dinamis (URL sitemap otomatis mengikuti domain yang diakses via helper `url()`, memenuhi FR-010/US2 Acceptance #2 — staging vs production beda domain tanpa perlu edit manual).

**Alternatives considered**: Suntik baris `Sitemap:` ke file statis saat deploy (mis. lewat command/script) — ditolak, tidak otomatis mengikuti domain per-environment tanpa konfigurasi tambahan per klien (melanggar semangat Principle I — starter kit dipakai lintas klien dengan domain berbeda-beda), dan tetap file statis sehingga rapuh terhadap perubahan domain.

## 3. Sumber data & aturan "bisa diakses publik" per jenis konten

**Decision**: Reuse persis logika visibilitas yang sudah ada di controller publik masing-masing modul (bukan query baru/terpisah):

| Sumber | Aturan visibilitas (disalin dari controller existing) |
|---|---|
| Produk | `Product::all()` — semua produk (ProductController::index, tidak ada flag aktif) |
| Artikel | `Article::whereNotNull('published_at')->where('published_at','<=',now())` (ArticleController::index) |
| Halaman Statis | `CustomPage::all()` — semua (CustomPageController, tidak ada flag) |
| Proyek Portfolio | `PortfolioProject::where('is_active', true)` (PortfolioController::index) |
| Halaman Karir | Disertakan hanya bila `BrandSettings::career_module_enabled` true (CareerController guard, AMC-212) |

**Rationale**: Sitemap yang akurat HARUS mencerminkan persis apa yang bisa diakses pengunjung — menduplikasi aturan yang sama dari controller publik (bukan menciptakan aturan baru) adalah satu-satunya cara menjamin konsistensi 1:1, dan sudah teruji lewat test existing masing-masing modul.

## 4. Format response (`Content-Type`, XML declaration)

**Decision**: `sitemap.xml` dikembalikan dengan header `Content-Type: application/xml; charset=UTF-8`; `robots.txt` dengan `Content-Type: text/plain; charset=UTF-8`. Keduanya lewat `response($view, 200, ['Content-Type' => ...])`, BUKAN `@extends('layouts.public')` (tidak perlu HTML/tema/aset sama sekali).

**Rationale**: Konten bukan HTML — memakai layout publik yang penuh aset (Vite, font, tema) untuk dokumen mesin-baca adalah pemborosan dan berisiko salah format; `Content-Type` XML/plain yang benar penting supaya crawler & validator memperlakukan respons sebagai dokumen sitemap/robots yang sah, bukan halaman error.

## Ringkasan keputusan

| # | Area | Keputusan | Dependency baru? |
|---|---|---|---|
| 1 | Generate sitemap.xml | Blade view XML manual, di-loop dari data existing | Tidak |
| 2 | `robots.txt` dinamis | Hapus file statis `public/robots.txt`, ganti route+controller | Tidak |
| 3 | Aturan visibilitas per konten | Reuse persis query controller publik existing | — |
| 4 | Format response | `Content-Type` eksplisit (XML/plain), tanpa layout HTML | Tidak |

Tidak ada [NEEDS CLARIFICATION] tersisa — semua keputusan konsisten dengan pola AMC-223 dan Principle V (nol dependency baru).
