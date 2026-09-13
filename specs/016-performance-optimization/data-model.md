# Phase 1 Data Model: Optimasi Performa Halaman Publik

## Tidak ada perubahan skema

Fitur ini tidak menambah/mengubah tabel, kolom, atau model Eloquent apa pun — murni perubahan cara tampilan (atribut HTML) dan lapisan cache di atas query yang sudah ada.

## Trait `App\Concerns\CachesPublicPages`

```php
trait CachesPublicPages
{
    private const CACHE_TTL = 300; // 5 menit — research.md §3

    protected function rememberPublicPage(string $key, \Closure $callback)
    {
        return \Illuminate\Support\Facades\Cache::remember($key, self::CACHE_TTL, $callback);
    }
}
```

Dipakai (`use CachesPublicPages;`) oleh 6 controller: `HomeController`, `ProductController`, `ArticleController`, `PortfolioController`, `FaqController`, `AboutController`.

## Skema cache key

| Pola key | Contoh | Sumber parameter |
|---|---|---|
| `public-page:home` | tetap | — |
| `public-page:produk.index` | tetap | — |
| `public-page:produk.show:{slug}` | `public-page:produk.show:panel-surya-550w` | `Product $product` (route model binding) |
| `public-page:artikel.index` | tetap | — |
| `public-page:artikel.show:{slug}` | `public-page:artikel.show:tips-hemat-listrik` | `Article $article` |
| `public-page:portfolio.index:{kategori}` | `public-page:portfolio.index:all` atau `public-page:portfolio.index:residensial` | `Request::query('kategori')`, default `'all'` bila kosong |
| `public-page:portfolio.show:{slug}` | `public-page:portfolio.show:atap-rumah-5kwp` | `PortfolioProject $portfolioProject` |
| `public-page:faq` | tetap | — |
| `public-page:tentang-kami` | tetap | — |

Detail rasional per keputusan lihat research.md §3.

## Atribut pemuatan gambar (bukan data tersimpan — kontrak render)

Setiap `<img>` di halaman publik mendapat salah satu dari dua perlakuan (lihat inventaris definitif research.md §2):

- **Eager** — tidak ada atribut `loading` tambahan (perilaku default browser).
- **Lazy** — `loading="lazy" decoding="async"`.

Tidak ada state/DB yang menyimpan keputusan ini — murni ditentukan oleh posisi `<img>` dalam template (di dalam komponen hero/cover teratas, atau tidak).

## Refactor kecil ikutan (bukan entity baru)

`components/sections/client-logos.blade.php` — logo klien saat ini dirender lewat string HTML manual (`$img = '<img ...>'; ... {!! $img !!}`). Direfactor jadi tag Blade `<img>` biasa (tanpa unescaped-HTML manual) supaya menambahkan `loading="lazy" decoding="async"` konsisten dengan pola file lain — pembersihan kecil yang muncul secara alami saat menyentuh file ini, bukan pekerjaan terpisah.
