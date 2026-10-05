<?php

namespace App\Models;

use App\Concerns\FlushesPublicPageCache;
use App\Concerns\HasSeoMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Tags\HasTags;

class Article extends Model
{
    use FlushesPublicPageCache;
    use HasFactory;
    use HasSeoMetadata;
    use HasTags;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'redaksi',
        'image_path',
        'image_caption',
        'article_category_id',
        'published_at',
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
            'published_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Estimasi waktu baca dalam menit (~200 kata/menit), minimal 1.
     */
    public function readingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->content));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * Baris produk terkait (untuk editor admin), berurutan.
     *
     * @return HasMany<ArticleRelatedProduct, $this>
     */
    public function relatedProductRows(): HasMany
    {
        return $this->hasMany(ArticleRelatedProduct::class)->orderBy('sort_order');
    }

    /**
     * Produk terkait yang ditampilkan di halaman detail, berurutan.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'article_product')
            ->withPivot('sort_order')
            ->orderBy('article_product.sort_order');
    }

    public function articleCategory(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    /**
     * Status turunan dari `published_at` — TIDAK disimpan sebagai kolom
     * terpisah, murni dihitung on-the-fly (data-model.md § Status turunan,
     * research.md §2).
     */
    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function isScheduled(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && ! $this->published_at->isFuture();
    }

    /**
     * @see HasSeoMetadata
     */
    protected function seoTitleFallback(): string
    {
        return $this->title;
    }

    protected function seoDescriptionFallback(): ?string
    {
        return $this->excerpt;
    }

    protected function seoImageFallbackUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
