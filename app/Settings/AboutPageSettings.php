<?php

namespace App\Settings;

use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class AboutPageSettings extends Settings
{
    public const DEFAULT_HERO_IMAGE_PATH = 'images/mockup/produk-1.jpg';

    public const DEFAULT_SIAPA_KAMI_IMAGE_PATH = 'images/mockup/tentang-kami-2.jpg';

    public const DEFAULT_NILAI_FEATURED_IMAGE_PATH = 'images/mockup/tentang-kami-3.jpg';

    public ?string $hero_image_path;

    public ?string $hero_subtitle;

    public ?string $siapa_kami_image_path;

    public ?string $siapa_kami_badge_text;

    public ?string $siapa_kami_eyebrow;

    public ?string $siapa_kami_heading;

    public ?string $siapa_kami_body;

    public ?string $siapa_kami_quote;

    public ?string $visi_eyebrow;

    public ?string $visi_heading;

    public ?string $visi_subtext;

    public ?string $misi_eyebrow;

    public ?string $misi_heading;

    public ?string $misi_subtext;

    /**
     * JSON-encoded array of {title, description}. Disimpan sebagai string
     * (bukan tipe array native) karena spatie/laravel-settings tidak
     * mendukung casting array bersarang (array of array) secara otomatis.
     */
    public ?string $misi_items;

    public ?string $nilai_heading;

    public ?string $nilai_subtext;

    public ?string $nilai_featured_image_path;

    public ?string $nilai_featured_icon;

    public ?string $nilai_featured_title;

    public ?string $nilai_featured_description;

    /**
     * JSON-encoded array of {icon, title, description}. Lihat catatan pada
     * `$misi_items`.
     */
    public ?string $nilai_items;

    /**
     * JSON-encoded array of {icon, value, label}. Lihat catatan pada
     * `$misi_items`.
     */
    public ?string $trust_items;

    public static function group(): string
    {
        return 'about_page';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function misiItems(): array
    {
        return json_decode($this->misi_items ?? '[]', true) ?: [];
    }

    /**
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    public function nilaiItems(): array
    {
        return json_decode($this->nilai_items ?? '[]', true) ?: [];
    }

    /**
     * @return array<int, array{icon: string, value: string, label: string}>
     */
    public function trustItems(): array
    {
        return json_decode($this->trust_items ?? '[]', true) ?: [];
    }

    public function heroImageUrl(): string
    {
        return $this->resolveImageUrl($this->hero_image_path, self::DEFAULT_HERO_IMAGE_PATH);
    }

    public function siapaKamiImageUrl(): string
    {
        return $this->resolveImageUrl($this->siapa_kami_image_path, self::DEFAULT_SIAPA_KAMI_IMAGE_PATH);
    }

    public function nilaiFeaturedImageUrl(): string
    {
        return $this->resolveImageUrl($this->nilai_featured_image_path, self::DEFAULT_NILAI_FEATURED_IMAGE_PATH);
    }

    /**
     * `siapa_kami_body` setelah dilewatkan `HtmlSanitizer` dan interpolasi
     * `{app_name}`, siap dirender dengan `{!! !!}`.
     */
    public function sanitizedSiapaKamiBody(string $appName): string
    {
        $clean = HtmlSanitizer::clean($this->siapa_kami_body);

        return str_replace('{app_name}', $appName, $clean);
    }

    /**
     * `siapa_kami_quote` setelah dilewatkan `HtmlSanitizer`, siap dirender
     * dengan `{!! !!}`.
     */
    public function sanitizedSiapaKamiQuote(): string
    {
        return HtmlSanitizer::clean($this->siapa_kami_quote);
    }

    private function resolveImageUrl(?string $path, string $defaultAsset): string
    {
        if (filled($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset($defaultAsset);
    }
}
