# Phase 1 Data Model: Sitemap & Robots Otomatis

## Tidak ada perubahan skema

Fitur ini murni membaca data yang sudah ada (lihat research.md §3) untuk menyusun satu dokumen XML — tidak ada migration, kolom, atau tabel baru.

## Bentuk data yang disusun controller (bukan entity tersimpan)

`SitemapController::xml()` mengumpulkan array/koleksi "entri URL" untuk dilempar ke view — setiap entri berbentuk:

| Field | Wajib? | Sumber |
|---|---|---|
| `loc` | ya | URL absolut (`url('/...')` atau `url('/produk/'.$product->slug)` dst.) |
| `lastmod` | opsional | `updated_at` model (format ISO 8601 / `toAtomString()`) — dilewati untuk halaman statis yang tidak punya kolom ini |

### Daftar sumber & urutan render

1. **Halaman statis tetap** (selalu ada): Beranda `/`, Tentang Kami `/tentang-kami`, Kontak `/kontak`, FAQ `/faq`, index Artikel `/artikel`, index Produk `/produk`, index Portfolio `/portfolio` — tanpa `lastmod`.
2. **Halaman Karir** `/karir` — disertakan HANYA bila `app(BrandSettings::class)->career_module_enabled` true.
3. **Produk** — `Product::all()`, tiap baris → `/produk/{slug}`, `lastmod` = `updated_at`.
4. **Artikel published** — `Article::whereNotNull('published_at')->where('published_at','<=',now())->get()`, tiap baris → `/artikel/{slug}`, `lastmod` = `updated_at`.
5. **Halaman Statis (Custom Page)** — `CustomPage::all()`, tiap baris → `/halaman/{slug}`, `lastmod` = `updated_at`.
6. **Proyek Portfolio aktif** — `PortfolioProject::where('is_active', true)->get()`, tiap baris → `/portfolio/{slug}`, `lastmod` = `updated_at`.

Query di atas adalah salinan persis dari query controller publik masing-masing modul (research.md §3) — bukan query independen baru, supaya cakupan sitemap selalu sinkron dengan apa yang benar-benar bisa diakses pengunjung.

## `robots.txt`

Bukan data model — dokumen teks statis-per-request:

```text
User-agent: *
Disallow:

Sitemap: {url('/sitemap.xml')}
```

Baris `User-agent`/`Disallow` identik dengan `public/robots.txt` yang lama (FR-011) — hanya baris `Sitemap:` yang baru, dihitung dinamis dari domain yang sedang diakses.
