<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public const DEFAULT_TITLE_SEPARATOR = '|';

    public const DEFAULT_TITLE_FORMAT = '{page_title} {separator} {site_name}';

    public const DEFAULT_SITEMAP_CHANGEFREQ = 'weekly';

    public const DEFAULT_SITEMAP_PRIORITY = '0.8';

    /**
     * Jenis halaman yang sah untuk `page_title_formats` (FR-026) — dibatasi
     * pada rute publik yang benar-benar ada di situs ini (research.md R0).
     * Tidak ada halaman kategori, tag, pencarian, maupun penulis.
     *
     * @var array<string, string>
     */
    public const PAGE_TYPES = [
        'home' => 'Beranda',
        'artikel_index' => 'Daftar Artikel',
        'artikel_show' => 'Detail Artikel',
        'produk_index' => 'Daftar Produk',
        'produk_show' => 'Detail Produk',
        'portfolio_index' => 'Daftar Portfolio',
        'portfolio_show' => 'Detail Proyek Portfolio',
        'halaman' => 'Halaman Kustom',
        'faq' => 'FAQ',
        'karir' => 'Karir',
        'kontak' => 'Kontak',
        'tentang_kami' => 'Tentang Kami',
    ];

    public string $title_separator;

    public string $default_title_format;

    /**
     * @var array<string, string>
     */
    public array $page_title_formats;

    public ?string $default_meta_description;

    /**
     * @var array<int, string>
     */
    public array $meta_keywords;

    public ?string $default_canonical_url;

    public bool $allow_indexing;

    public bool $allow_following;

    public ?string $twitter_handle;

    public ?string $additional_head_meta;

    public ?string $verification_google;

    public ?string $verification_bing;

    public ?string $verification_yandex;

    public ?string $verification_baidu;

    public ?string $robots_txt_content;

    public bool $sitemap_enabled;

    public bool $sitemap_include_pages;

    public bool $sitemap_include_articles;

    public bool $sitemap_include_products;

    public bool $sitemap_include_portfolio;

    public ?string $sitemap_changefreq;

    public ?string $sitemap_priority;

    public static function group(): string
    {
        return 'seo';
    }
}
