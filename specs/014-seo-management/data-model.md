# Phase 1 Data Model: SEO Management

## Perubahan skema

Tidak ada tabel baru. Menambah 3 kolom nullable yang identik ke 4 tabel existing:

| Kolom | Tipe | Nullable | Catatan |
|---|---|---|---|
| `meta_title` | `string(255)` | ya | Judul pencarian kustom. Kosong → fallback ke judul/nama konten. |
| `meta_description` | `text` | ya | Deskripsi pencarian kustom. Kosong → fallback dipotong 160 karakter dari ringkasan/konten. |
| `meta_image_path` | `string(255)` | ya | Path relatif WebP (disk `public`) hasil `ImageUploads::storeAsWebp(maxWidth: 1200)`. Kosong → fallback ke gambar utama konten, lalu ke `BrandSettings::og_image_path`. |

Migrasi (4 file, pola `add_seo_fields_to_{table}_table`):

- `products` (menambah ke tabel `products`)
- `articles`
- `custom_pages`
- `portfolio_projects`

```php
Schema::table('{table}', function (Blueprint $table) {
    $table->string('meta_title')->nullable()->after('{kolom terakhir existing}');
    $table->text('meta_description')->nullable()->after('meta_title');
    $table->string('meta_image_path')->nullable()->after('meta_description');
});
```

## Perubahan `BrandSettings` (Spatie Settings)

Migrasi settings baru `add_meta_description_to_brand_settings` (pola sama seperti `add_whatsapp_and_notification_email_to_brand_settings`):

| Properti | Tipe | Default |
|---|---|---|
| `meta_description` | `?string` | `null` (fallback ke string hardcoded existing di layout bila kosong) |

`og_image_path` dan `app_name` sudah ada — dipakai apa adanya sebagai fallback judul & gambar OG situs (tidak ada perubahan).

## Trait `App\Concerns\HasSeoMetadata`

Dipakai oleh: `Product`, `Article`, `CustomPage`, `PortfolioProject`.

```php
trait HasSeoMetadata
{
    public function seoTitle(): string
    {
        return $this->meta_title ?: $this->seoTitleFallback();
    }

    public function seoDescription(): string
    {
        return Str::limit(strip_tags($this->meta_description ?: (string) $this->seoDescriptionFallback()), 160);
    }

    public function seoImageUrl(): ?string
    {
        if ($this->meta_image_path) {
            return Storage::disk('public')->url($this->meta_image_path);
        }

        return $this->seoImageFallbackUrl();
    }

    abstract protected function seoTitleFallback(): string;
    abstract protected function seoDescriptionFallback(): ?string;
    abstract protected function seoImageFallbackUrl(): ?string;
}
```

### Implementasi fallback per model

| Model | `seoTitleFallback()` | `seoDescriptionFallback()` | `seoImageFallbackUrl()` |
|---|---|---|---|
| `Product` | `$this->name` | `$this->short_description` | `$this->coverImageUrl()` *(sudah ada)* |
| `Article` | `$this->title` | `$this->excerpt` | `$this->image_path ? Storage::disk('public')->url($this->image_path) : null` |
| `CustomPage` | `$this->title` | `$this->content` *(di-`strip_tags`+limit oleh trait)* | `null` *(tidak ada field gambar — jatuh ke `BrandSettings::og_image_path` di layer Blade)* |
| `PortfolioProject` | `$this->title` | `$this->description` | `$this->coverImageUrl()` *(sudah ada)* |

`seoImageUrl()` yang mengembalikan `null` (mis. CustomPage tanpa `meta_image_path`) ditangani di Blade: `@section('og_image', $page->seoImageUrl() ?? $brand->ogImageUrl())`.

## Helper `App\Support\Seo\JsonLd`

Static builder, tidak menyimpan state, tidak butuh migrasi:

```php
class JsonLd
{
    public static function organization(BrandSettings $brand): array;   // @type Organization
    public static function faqPage(Collection $faqItems): ?array;       // null bila $faqItems kosong (FR-009)
    public static function article(Article $article): array;            // @type Article
    public static function product(Product $product): array;            // @type Product, `offers` hanya bila price terisi (FR-011)
}
```

`FaqPage`/`Article`/`Product` masing-masing dipanggil dari controller (bukan langsung di Blade) supaya gampang di-unit-test tanpa perlu render view — controller assign hasil `json_encode(...)` ke variabel view, mis. `'schema' => JsonLd::product($product)`.

## Relasi & tidak ada perubahan lain

- Tidak ada relasi baru antar entity.
- Tidak ada perubahan pada model/tabel di luar 4 model + `BrandSettings` di atas.
- Tidak ada state transition (semua field SEO independen, tidak ada status/workflow).
