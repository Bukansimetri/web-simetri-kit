# Phase 0 Research: Optimasi Performa Halaman Publik

## 1. Mekanisme lazy-load gambar

**Decision**: Atribut HTML bawaan browser `loading="lazy" decoding="async"` pada tiap `<img>` yang BUKAN gambar hero/cover pertama halaman. Tidak ada JavaScript/pustaka tambahan.

**Rationale**: Native lazy-loading (`loading="lazy"`) didukung seluruh browser modern, browser lama otomatis mengabaikan atribut yang tidak dikenal dan tetap memuat gambar seperti biasa (graceful degradation gratis, memenuhi FR-004 tanpa logic tambahan). `decoding="async"` mencegah decode gambar memblokir main thread saat gambar akhirnya dimuat. Penting: browser HANYA menunda gambar yang benar-benar di luar area pandang saat page load — gambar `loading="lazy"` yang kebetulan sudah terlihat di layar (mis. item pertama grid produk) tetap dimuat segera oleh browser tanpa perlu ditandai eager secara manual; ini menyederhanakan aturan jadi satu kalimat: **"eager HANYA untuk gambar di dalam komponen hero/cover teratas tiap halaman, lazy untuk selebihnya"** — tidak perlu logic "N item pertama grid = eager".

**Alternatives considered**: Pustaka JS Intersection Observer kustom / lazysizes — ditolak, native browser API sudah cukup dan gratis (nol dependency, Principle V); logic "tandai manual N gambar pertama tiap grid sebagai eager" — ditolak, tidak perlu karena browser sudah menangani ini otomatis lewat viewport-intersection bawaan `loading="lazy"`.

## 2. Inventaris gambar: eager vs lazy per file

**Decision**: Berdasarkan audit `grep <img>` di seluruh `resources/views/`, daftar definitif berikut (dipakai langsung sebagai acuan tasks.md):

| File | Baris | Konteks | Perlakuan |
|---|---|---|---|
| `components/sections/hero.blade.php` | 14 | Hero statis beranda | **Eager** (tanpa atribut `loading`) |
| `components/sections/page-hero.blade.php` | 14 | Hero halaman dalam (Produk index, Karir, dll.) | **Eager** |
| `components/sections/banner-carousel.blade.php` | 15/19 (single banner) | Banner tunggal di posisi hero | **Eager** |
| `components/sections/banner-carousel.blade.php` | 43/47, iterasi `$i === 0` | Slide pertama carousel (posisi hero) | **Eager** |
| `components/sections/banner-carousel.blade.php` | 43/47, iterasi `$i > 0` | Slide carousel berikutnya (tersembunyi `x-show`, bukan tampilan awal) | **Lazy** |
| `pages/tentang-kami.blade.php` | 34 | Hero bespoke di atas halaman Tentang Kami | **Eager** |
| `pages/produk/show.blade.php` | 23 | Gambar galeri utama (sudah `:src` binding Alpine, tanpa `src` statis — TIDAK disentuh) | **Eager** (tidak perlu atribut, sudah bukan kandidat lazy) |
| `pages/portfolio/show.blade.php` | 25 | Gambar sampul utama di atas halaman detail proyek | **Eager** |
| `components/sections/product-card.blade.php` | 6 | Thumbnail kartu produk (grid) | **Lazy** |
| `components/sections/testimonials.blade.php` | 30 | Foto avatar testimoni | **Lazy** |
| `components/sections/client-logos.blade.php` | 16 | Logo klien (di-refactor dari string HTML manual ke tag Blade biasa — lihat §5) | **Lazy** |
| `components/sections/article-card.blade.php` | 6 | Thumbnail kartu artikel | **Lazy** |
| `components/sections/team-members.blade.php` | 18 | Foto anggota tim | **Lazy** |
| `pages/home.blade.php` | 49 | Gambar section "Produk Kami" | **Lazy** |
| `pages/home.blade.php` | 109 | Avatar testimoni (markup terpisah dari komponen `testimonials`) | **Lazy** |
| `pages/artikel/index.blade.php` | 41 | Gambar artikel unggulan (featured) — bukan di dalam komponen hero | **Lazy** |
| `pages/tentang-kami.blade.php` | 52, 126 | Gambar section "Siapa Kami" & "Ekonomi Hijau" | **Lazy** |
| `pages/artikel/show.blade.php` | 32 | Gambar sampul artikel detail | **Lazy** *(di bawah breadcrumb+judul, bukan hero gambar — lihat catatan)* |
| `pages/portfolio/index.blade.php` | 45 | Thumbnail kartu proyek (grid) | **Lazy** |
| `pages/portfolio/show.blade.php` | 31 | Thumbnail galeri tambahan | **Lazy** |
| `pages/produk/show.blade.php` | 34 | Thumbnail galeri (Alpine `:src` binding, tanpa `src` statis) | Tidak disentuh (bukan kandidat `loading`, sudah bukan bagian gambar pertama) |

*Catatan `artikel/show.blade.php:32`*: halaman ini memakai breadcrumb+judul teks di atas (bukan gambar hero), gambar sampul artikel baru muncul setelah itu — mengikuti aturan "hero HARUS berupa komponen/section gambar besar di paling atas", gambar ini **lazy** secara konsisten dengan aturan, bukan pengecualian.

## 3. Caching halaman publik read-only

**Decision**: `Cache::remember($key, 300, $callback)` (TTL 5 menit/300 detik) membungkus query di 8 method controller publik berikut, lewat trait kecil `App\Concerns\CachesPublicPages` yang menyediakan `rememberPublicPage(string $key, \Closure $callback)`:

| Controller::method | Cache key |
|---|---|
| `HomeController::__invoke` | `public-page:home` |
| `ProductController::index` | `public-page:produk.index` |
| `ProductController::show` | `public-page:produk.show:{slug}` |
| `ArticleController::index` | `public-page:artikel.index` |
| `ArticleController::show` | `public-page:artikel.show:{slug}` |
| `PortfolioController::index` | `public-page:portfolio.index:{kategori atau 'all'}` |
| `PortfolioController::show` | `public-page:portfolio.show:{slug}` |
| `FaqController::__invoke` | `public-page:faq` |
| `AboutController::__invoke` | `public-page:tentang-kami` |

**Rationale**: `CACHE_STORE` sudah dikonfigurasi (`database`, lihat `config/cache.php`) — reuse infrastruktur yang sudah ada, nol dependency baru. TTL 5 menit dipilih sebagai trade-off wajar: cukup lama untuk memberi manfaat cache-hit nyata pada trafik berulang (SC-003), cukup singkat supaya perubahan admin terlihat publik "dalam hitungan menit" (SC-004/FR-007) tanpa perlu membangun mekanisme invalidasi berbasis event (Assumptions spec.md — ditolak demi kesederhanaan, Principle V). `PortfolioController::index` butuh key per-`kategori` karena TIDAK seperti Produk/Artikel (filter kategori di sana murni client-side Alpine atas satu query penuh), Portfolio **memfilter di server** lewat query string `?kategori=` — cache key yang salah (tanpa parameter ini) akan membocorkan hasil filter kategori A ke pengunjung yang minta kategori B.

Method controller yang TIDAK dibungkus cache sama sekali (FR-006): `ContactController::show/store`, `CalculatorController::storeLead`, `CareerController::__invoke` (di luar cakupan spec — lihat Assumptions), `CustomPageController::__invoke` (di luar cakupan spec), `SitemapController::xml/robots` (AMC-224, dokumen mesin-baca yang sudah wajib real-time per definisinya).

**Kenapa admin panel otomatis tidak terpengaruh (FR-008)**: Filament Resource (`ProductResource`, `ArticleResource`, dst.) punya jalur query Eloquent sendiri yang SAMA SEKALI TERPISAH dari 8 controller publik di atas — cache HANYA dipasang di controller publik, bukan di level model/repository. Admin yang menyimpan perubahan lewat Filament selalu membaca langsung dari database tanpa melewati cache manapun — jadi FR-008 terpenuhi by design, tanpa perlu logic pengecualian tambahan.

**Alternatives considered**: Cache tags + invalidasi otomatis saat model disimpan (`Model::saved()` event/Observer) — ditolak untuk iterasi ini (Assumptions spec.md eksplisit menolak invalidasi instan demi kesederhanaan); TTL lebih panjang (mis. 1 jam) — ditolak, terlalu lama untuk SC-004 ("dalam hitungan menit"); TTL sangat pendek (mis. 30 detik) — ditolak, manfaat cache-hit terlalu kecil untuk berarti secara performa.

## 4. Trait `CachesPublicPages`

**Decision**: `app/Concerns/CachesPublicPages.php` — satu trait dipakai `use` di 6 controller (`HomeController`, `ProductController`, `ArticleController`, `PortfolioController`, `FaqController`, `AboutController`), method `protected function rememberPublicPage(string $key, \Closure $callback)` membungkus `Cache::remember($key, self::CACHE_TTL, $callback)`.

**Rationale**: Menghindari duplikasi angka TTL (300) dan pemanggilan `Cache::remember` mentah di 8 tempat — pola sama seperti trait `HasSeoMetadata` (AMC-223) yang sudah established di codebase ini untuk "perilaku kecil dipakai bersama lintas kelas". Satu tempat untuk mengubah TTL di masa depan bila diperlukan.

**Alternatives considered**: Service/helper class statis (`App\Support\Cache\PublicPageCache::remember(...)`) — dipertimbangkan, tapi trait lebih pas karena controller-controller ini memang secara alami "punya" perilaku caching-nya sendiri (bukan operasi lintas-domain yang butuh kelas independen); menaruh langsung `Cache::remember(...)` inline di 8 tempat tanpa abstraksi — ditolak, duplikasi angka TTL 8× berisiko drift saat salah satu lupa diperbarui.

## 5. Pemuatan non-blocking stylesheet font ikon

**Decision**: Ganti tag `<link href="...Material+Symbols..." rel="stylesheet">` di `layouts/public.blade.php` dengan teknik swap standar: `<link rel="stylesheet" href="..." media="print" onload="this.media='all'">` + `<noscript><link rel="stylesheet" href="..."></noscript>` sebagai fallback.

**Rationale**: Teknik `media="print"` + `onload` swap adalah pola umum (direkomendasikan berbagai panduan performa web) untuk memuat CSS non-esensial tanpa memblokir render — browser tetap mengunduh file di background (prioritas rendah, `media="print"` tidak dianggap relevan untuk render layar sehingga tidak memblokir), lalu `onload` mengubah `media` jadi `all` begitu siap sehingga style diterapkan. `<noscript>` fallback memastikan pengunjung dengan JavaScript nonaktif tetap mendapat stylesheet (dengan trade-off tetap blocking untuk mereka — dapat diterima, edge case FR-010 tetap terpenuhi karena ikon tetap tampil & berfungsi, hanya tidak non-blocking untuk populasi kecil ini).

**Alternatives considered**: `rel="preload" as="style"` + `onload` swap — pola alternatif yang setara, ditolak hanya karena `media="print"` sedikit lebih ringkas (satu bentuk tag, bukan resource-hint tambahan) untuk kasus sesederhana ini; men-download & self-host subset ikon yang benar-benar dipakai (mis. hanya ikon yang muncul di kode) — ditolak, di luar cakupan (spec.md Assumptions eksplisit membatasi ke "cara pemuatan", bukan mengganti sumber/isi font).

## Ringkasan keputusan

| # | Area | Keputusan | Dependency baru? |
|---|---|---|---|
| 1 | Mekanisme lazy-load | Atribut native `loading="lazy" decoding="async"` | Tidak |
| 2 | Cakupan eager vs lazy | Tabel definitif §2 (hero/cover = eager, sisanya = lazy) | — |
| 3 | Caching halaman publik | `Cache::remember` TTL 300 detik, 9 cache key, disk `database` yang sudah ada | Tidak |
| 4 | Helper caching | Trait `CachesPublicPages` (pola sama `HasSeoMetadata`) | Tidak |
| 5 | Non-blocking font ikon | `media="print"` + `onload` swap + `<noscript>` fallback | Tidak |

Tidak ada [NEEDS CLARIFICATION] tersisa — seluruh keputusan didasarkan audit kode nyata dan konsisten Principle V (nol dependency baru).
