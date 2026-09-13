<?php

namespace App\Models;

use App\Concerns\HasSeoMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPage extends Model
{
    use HasFactory;
    use HasSeoMetadata;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_image_path',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Kontrak "internal linkable" untuk Menu Builder (spec 017-menu-builder).
     */
    public function getPublicUrl(): ?string
    {
        return route('halaman.show', $this);
    }

    public function getMenuLabelAttribute(): string
    {
        return $this->title;
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
        return $this->content;
    }

    protected function seoImageFallbackUrl(): ?string
    {
        // CustomPage tidak punya field gambar konten — fallback lanjut ke
        // gambar OG default situs ditangani di layer Blade (T029-T032).
        return null;
    }
}
