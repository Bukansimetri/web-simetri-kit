<?php

namespace App\Models;

use App\Enums\BannerOverlayStyle;
use App\Enums\BannerTextPosition;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Banner extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'image_path',
        'alt_text',
        'link_url',
        'starts_at',
        'ends_at',
        'order',
        'is_active',
        'badge_text',
        'heading',
        'subheading',
        'cta_primary_label',
        'cta_primary_url',
        'cta_secondary_label',
        'cta_secondary_url',
        'trust_html',
        'overlay_style',
        'text_position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'order' => 'integer',
            'is_active' => 'boolean',
            'overlay_style' => BannerOverlayStyle::class,
            'text_position' => BannerTextPosition::class,
        ];
    }

    /**
     * Buang cache beranda setiap kali banner disimpan atau dihapus, lewat
     * jalur mana pun — form Filament, ToggleColumn, aksi massal, maupun
     * reorder — sehingga perubahan admin langsung terlihat (FR-018, R5).
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('public-page:home'));
        static::deleted(fn () => Cache::forget('public-page:home'));
    }

    /**
     * Banner yang sedang tayang: aktif DAN dalam periode tayang (inklusif).
     * Periode kosong = tanpa batas pada sisi tsb (FR-008, data-model.md).
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Status tayang turunan untuk kolom admin (FR-013). Konsisten dengan
     * scopeLive(): mengembalikan 'live' tepat ketika banner termasuk hasil
     * scopeLive().
     */
    public function displayStatus(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at !== null && $this->starts_at->gt(today())) {
            return 'scheduled';
        }

        if ($this->ends_at !== null && $this->ends_at->lt(today())) {
            return 'expired';
        }

        return 'live';
    }

    /**
     * Benar bila slide punya konten selain gambar — badge, judul, subjudul,
     * salah satu CTA, atau trust bar — dipakai view untuk memutuskan apakah
     * blok konten dirender sama sekali (FR-002, contracts/public-render.md §2).
     */
    public function hasContent(): bool
    {
        return filled($this->badge_text)
            || filled($this->heading)
            || filled($this->subheading)
            || $this->hasCta()
            || filled($this->trust_html);
    }

    /**
     * Benar bila minimal satu pasangan CTA (label + alamat) lengkap. Dipakai
     * untuk aturan tautan majemuk: slide dengan CTA tidak lagi membungkus
     * gambar dengan `link_url` (R8, FR-017).
     */
    public function hasCta(): bool
    {
        return $this->hasPrimaryCta() || $this->hasSecondaryCta();
    }

    /**
     * Benar bila CTA utama (label + alamat) lengkap.
     */
    public function hasPrimaryCta(): bool
    {
        return filled($this->cta_primary_label) && filled($this->cta_primary_url);
    }

    /**
     * Benar bila CTA sekunder (label + alamat) lengkap.
     */
    public function hasSecondaryCta(): bool
    {
        return filled($this->cta_secondary_label) && filled($this->cta_secondary_url);
    }

    /**
     * `trust_html` yang telah dilewatkan `HtmlSanitizer`, siap dirender
     * dengan `{!! !!}` (FR-007). Null bila `trust_html` kosong.
     */
    public function sanitizedTrustHtml(): ?string
    {
        if (blank($this->trust_html)) {
            return null;
        }

        $clean = HtmlSanitizer::clean($this->trust_html);

        return blank($clean) ? null : $clean;
    }
}
