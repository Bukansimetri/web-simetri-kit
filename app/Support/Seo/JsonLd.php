<?php

namespace App\Support\Seo;

use App\Models\Article;
use App\Models\FaqItem;
use App\Models\Product;
use App\Settings\AppearanceSettings;
use App\Settings\SiteSettings;
use Illuminate\Support\Collection;
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
    public static function organization(SiteSettings $site, AppearanceSettings $appearance): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $site->site_name ?: config('app.name'),
            'url' => url('/'),
        ];

        if (filled($appearance->logo_path)) {
            $schema['logo'] = Storage::disk('public')->url($appearance->logo_path);
        }

        return $schema;
    }

    /**
     * Markup `FAQPage` — dilewati total (return null) bila `$faqItems`
     * kosong, supaya tidak menyisipkan hasil pencarian kosong/menyesatkan
     * (AMC-223 FR-009).
     *
     * @param  Collection<int, FaqItem>  $faqItems
     * @return array<string, mixed>|null
     */
    public static function faqPage(Collection $faqItems): ?array
    {
        if ($faqItems->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqItems->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item->answer,
                ],
            ])->values()->all(),
        ];
    }

    /**
     * Markup `Article` untuk halaman detail artikel published (AMC-223
     * FR-010). Memakai fallback SEO (`seoTitle()`/`seoImageUrl()`) dari
     * trait HasSeoMetadata — lihat research.md §3 & Dependencies di
     * tasks.md soal urutan build US2→US3.
     *
     * @return array<string, mixed>
     */
    public static function article(Article $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->seoTitle(),
            'image' => $article->seoImageUrl(),
            'datePublished' => $article->published_at?->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => $article->redaksi ?: (app(SiteSettings::class)->site_name ?: config('app.name')),
            ],
        ];
    }

    /**
     * Markup `Product` untuk halaman detail produk (AMC-223 FR-011).
     * `offers` HANYA disertakan bila `price` terisi — tidak ada klaim
     * `availability` (Assumptions: bukan situs checkout).
     *
     * @return array<string, mixed>
     */
    public static function product(Product $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->seoTitle(),
            'description' => $product->seoDescription(),
            'image' => $product->seoImageUrl(),
        ];

        if (filled($product->price)) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => (string) $product->price,
                'priceCurrency' => 'IDR',
            ];
        }

        return $schema;
    }
}
