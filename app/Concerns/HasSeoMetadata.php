<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Field SEO opsional (`meta_title`, `meta_description`, `meta_image_path`)
 * yang bisa dipasang di model konten mana pun. Tiap field jatuh ke fallback
 * independen bila kosong (AMC-223 FR-006) — model pemakai HANYA perlu
 * mendeklarasikan sumber data fallback-nya sendiri lewat 3 method abstrak
 * di bawah; logika limit/strip-tags dipusatkan di sini (data-model.md §
 * trait HasSeoMetadata).
 */
trait HasSeoMetadata
{
    public function seoTitle(): string
    {
        return $this->meta_title ?: $this->seoTitleFallback();
    }

    public function seoDescription(): string
    {
        return Str::limit(strip_tags((string) ($this->meta_description ?: $this->seoDescriptionFallback())), 160);
    }

    public function seoImageUrl(): ?string
    {
        if (filled($this->meta_image_path)) {
            return Storage::disk('public')->url($this->meta_image_path);
        }

        return $this->seoImageFallbackUrl();
    }

    abstract protected function seoTitleFallback(): string;

    abstract protected function seoDescriptionFallback(): ?string;

    abstract protected function seoImageFallbackUrl(): ?string;
}
