<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Article extends Model
{
    use HasFactory;
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
}
