<?php

namespace App\Models;

use App\Concerns\HasSeoMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Tags\HasTags;

class Article extends Model
{
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
