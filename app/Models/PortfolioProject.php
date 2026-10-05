<?php

namespace App\Models;

use App\Concerns\FlushesPublicPageCache;
use App\Concerns\HasSeoMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioProject extends Model
{
    use FlushesPublicPageCache;
    use HasFactory;
    use HasSeoMetadata;

    /**
     * Default kosong untuk kolom json `images` — supaya form yang belum
     * menyertakan gambar tetap bisa disimpan tanpa error NOT NULL.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'images' => '[]',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'portfolio_category_id',
        'title',
        'slug',
        'description',
        'images',
        'client_name',
        'project_url',
        'completed_at',
        'order',
        'is_active',
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
            'images' => 'array',
            'completed_at' => 'date',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function portfolioCategory(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class);
    }

    /**
     * Seluruh URL galeri gambar, urut sesuai `images`.
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path) => Storage::disk('public')->url($path))
            ->all();
    }

    /**
     * URL gambar sampul (gambar pertama). Null bila belum ada gambar.
     */
    public function coverImageUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    /**
     * Ringkasan satu paragraf untuk kartu daftar, diturunkan dari deskripsi.
     */
    public function summary(): string
    {
        $plain = html_entity_decode(strip_tags((string) $this->description), ENT_QUOTES | ENT_HTML5);

        return Str::of($plain)->squish()->limit(137)->toString();
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
        return $this->description;
    }

    protected function seoImageFallbackUrl(): ?string
    {
        return $this->coverImageUrl();
    }
}
