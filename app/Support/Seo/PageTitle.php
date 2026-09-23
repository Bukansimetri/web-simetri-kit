<?php

namespace App\Support\Seo;

use App\Settings\SeoSettings;
use App\Settings\SiteSettings;

/**
 * Penyusun judul `<title>` berbasis pola (FR-025 sampai FR-028,
 * spec 023-site-settings). Dipusatkan di sini (bukan langsung di Blade)
 * supaya penggantian placeholder dan aturan "penanda tak dikenal dibuang"
 * hanya punya satu jalur, mengikuti pola App\Support\Seo\JsonLd.
 */
class PageTitle
{
    /**
     * Judul untuk halaman statis tanpa pengaturan SEO per konten (Beranda,
     * Kontak, FAQ, Karir, Tentang Kami, daftar Artikel/Produk/Portfolio).
     */
    public static function forStatic(string $pageType, string $label): string
    {
        return self::compose($pageType, $label);
    }

    /**
     * Judul untuk konten yang punya pengaturan SEO sendiri (Produk, Artikel,
     * Portfolio, Halaman Kustom). `$metaTitle` MUST berupa nilai mentah
     * (belum di-fallback) — bila terisi, MENANG ATAS pola apa pun (FR-028).
     */
    public static function forContent(string $pageType, ?string $metaTitle, string $fallbackLabel): string
    {
        if (filled($metaTitle)) {
            return $metaTitle;
        }

        return self::compose($pageType, $fallbackLabel);
    }

    private static function compose(string $pageType, string $pageTitle): string
    {
        $seo = app(SeoSettings::class);
        $site = app(SiteSettings::class);

        $format = $seo->page_title_formats[$pageType] ?? null;
        $format = filled($format) ? $format : $seo->default_title_format;

        $separator = $seo->title_separator;

        $result = strtr($format, [
            '{page_title}' => $pageTitle,
            '{site_name}' => $site->site_name ?: config('app.name'),
            '{separator}' => $separator,
        ]);

        // Penanda isian tak dikenal dibuang (FR-027) — hanya {page_title},
        // {site_name}, {separator} yang dikenali di atas.
        $result = preg_replace('/\{[a-zA-Z0-9_]+\}/', '', $result);

        if ($separator === '') {
            return trim(preg_replace('/\s+/', ' ', $result));
        }

        // Pecah berdasarkan pemisah lalu buang segmen kosong (placeholder
        // yang gugur) sebelum menyambung ulang — ini menangani pemisah
        // ganda di tengah maupun yang menggantung di awal/akhir sekaligus,
        // bukan hanya kasus di ujung string (FR-027).
        $segments = preg_split('/\s*'.preg_quote($separator, '/').'\s*/', $result);
        $segments = array_values(array_filter(
            array_map(fn (string $segment) => trim(preg_replace('/\s+/', ' ', $segment)), $segments),
            fn (string $segment) => $segment !== ''
        ));

        return implode(' '.$separator.' ', $segments);
    }
}
