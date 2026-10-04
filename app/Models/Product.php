<?php

namespace App\Models;

use App\Concerns\FlushesPublicPageCache;
use App\Concerns\HasSeoMetadata;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use FlushesPublicPageCache;
    use HasFactory;
    use HasSeoMetadata;

    /**
     * Batas produk yang bisa ditampilkan di section "Solusi Untuk Setiap Kebutuhan" Beranda.
     */
    public const HOME_LIMIT = 3;

    /**
     * Default kosong untuk kolom json — supaya form Filament yang belum
     * menyertakan field ini (mis. create dasar di US2, sebelum galeri/specs/
     * fitur ditambahkan di US3/US4) tetap bisa menyimpan tanpa error NOT NULL.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'images' => '[]',
        'specs' => '[]',
        'features' => '[]',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'category_id',
        'short_description',
        'description',
        'price',
        'strikethrough_price',
        'images',
        'specs',
        'features',
        'order',
        'show_on_home',
        'meta_title',
        'meta_description',
        'meta_image_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'strikethrough_price' => 'decimal:2',
            'images' => 'array',
            'specs' => 'array',
            'features' => 'array',
            'order' => 'integer',
            'show_on_home' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Produk untuk section Beranda: yang ditandai admin (urut `order`), atau 3 teratas bila belum ada yang ditandai.
     *
     * @return Collection<int, static>
     */
    public static function forHome(): Collection
    {
        $featured = static::query()
            ->where('show_on_home', true)
            ->orderBy('order')
            ->orderBy('id')
            ->take(self::HOME_LIMIT)
            ->get();

        if ($featured->isNotEmpty()) {
            return $featured;
        }

        return static::query()->orderBy('order')->orderBy('id')->take(self::HOME_LIMIT)->get();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * URL gambar sampul (cover) — gambar pertama dalam urutan galeri (FR-011).
     * Null kalau produk belum punya gambar sama sekali (edge case placeholder,
     * ditangani di Blade — lihat FR-018).
     */
    public function coverImageUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    /**
     * Seluruh URL galeri gambar, urut sesuai `images` (FR-010).
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path) => str_starts_with($path, 'images/')
                ? asset($path)              // aset demo statis (public/images/…) dari seeder
                : Storage::disk('public')->url($path))
            ->all();
    }

    /**
     * @see HasSeoMetadata
     */
    protected function seoTitleFallback(): string
    {
        return $this->name;
    }

    protected function seoDescriptionFallback(): ?string
    {
        return $this->short_description;
    }

    protected function seoImageFallbackUrl(): ?string
    {
        return $this->coverImageUrl();
    }
}
