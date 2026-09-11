<?php

namespace App\Support\Seo;

use App\Settings\BrandSettings;
use Illuminate\Support\Facades\Storage;

/**
 * Kumpulan static builder JSON-LD (schema.org) — mengembalikan array PHP
 * siap `json_encode`, dirender lewat komponen <x-seo.json-ld>. Dipusatkan
 * di sini (bukan langsung di Blade) supaya struktur tiap `@type` mudah
 * di-unit-test tanpa perlu render view (lihat AMC-223 research.md §6).
 */
class JsonLd
{
    /**
     * Markup `Organization` global — dirender di setiap halaman publik.
     *
     * @return array<string, mixed>
     */
    public static function organization(BrandSettings $brand): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $brand->app_name ?: config('app.name'),
            'url' => url('/'),
        ];

        if (filled($brand->logo_path)) {
            $schema['logo'] = Storage::disk('public')->url($brand->logo_path);
        }

        return $schema;
    }
}
