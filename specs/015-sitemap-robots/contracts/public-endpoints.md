# Contract: Endpoint Publik Sitemap & Robots

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-13

Dua endpoint publik baru, tanpa autentikasi, tanpa payload request.

## 1. `GET /sitemap.xml`

| Aspek | Kontrak |
|---|---|
| Status | MUST selalu 200 selama aplikasi bisa diakses (tidak pernah 404/500 akibat data kosong — edge case "situs baru tanpa konten") (FR-012, Edge Cases) |
| `Content-Type` | `application/xml; charset=UTF-8` |
| Root element | `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">` |
| Isi | Satu `<url><loc>...</loc>[<lastmod>...</lastmod>]</url>` per entri di data-model.md § Daftar sumber |
| Halaman statis tetap | 7 URL selalu ada: `/`, `/tentang-kami`, `/kontak`, `/faq`, `/artikel`, `/produk`, `/portfolio` (FR-002) |
| `/karir` | Ada HANYA bila `BrandSettings::career_module_enabled` true (FR-003) |
| Produk | Satu `<url>` per baris `Product::all()` → `/produk/{slug}` (FR-004) |
| Artikel | Satu `<url>` per Artikel published (`published_at` terisi & ≤ sekarang) → `/artikel/{slug}`; draft/terjadwal TIDAK muncul (FR-005) |
| Halaman Statis | Satu `<url>` per `CustomPage::all()` → `/halaman/{slug}` (FR-006) |
| Portfolio | Satu `<url>` per `PortfolioProject::where('is_active', true)` → `/portfolio/{slug}`; nonaktif TIDAK muncul (FR-007) |
| `loc` | MUST URL absolut (skema + domain) — `url(...)`, bukan path relatif (FR-008) |
| Karakter spesial di slug/nama | MUST ter-escape sebagai entity XML yang valid (mis. `&` → `&amp;`) — dokumen tetap satu XML valid (Edge Cases) |
| Kesegaran data | MUST dihitung ulang setiap request — tambah/hapus/ubah status konten sebelumnya MUST langsung tercermin di request berikutnya, tanpa cache/build step (FR-009) |
| Setiap `loc` | MUST, saat diakses langsung, mengembalikan 200 (SC-001) — divalidasi lewat test yang benar-benar melakukan HTTP GET ke tiap URL hasil generate |

## 2. `GET /robots.txt`

| Aspek | Kontrak |
|---|---|
| Status | 200 |
| `Content-Type` | `text/plain; charset=UTF-8` |
| Baris `User-agent`/`Disallow` | IDENTIK dengan `public/robots.txt` lama: `User-agent: *` lalu `Disallow:` (kosong = izinkan semua) — TIDAK berubah (FR-011) |
| Baris `Sitemap:` | MUST ada, nilainya URL absolut ke `/sitemap.xml` pada domain yang sedang diakses (`url('/sitemap.xml')`) — bukan hardcoded domain lain (FR-010, US2 Acceptance #2) |
| File statis lama | `public/robots.txt` MUST dihapus — bila masih ada, web server akan menyajikannya langsung dan route ini TIDAK PERNAH tereksekusi (research.md §2) |

## 3. Non-goal eksplisit

- Tidak ada endpoint/route baru untuk mengelola isi sitemap dari admin panel — sepenuhnya derivatif dari data existing (Assumptions spec.md).
- Tidak ada versi sitemap index / multi-file — satu `sitemap.xml` tunggal (Assumptions spec.md).
