<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CustomPage;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Aturan bawaan yang aman (FR-037) — tersaji saat admin belum mengisi
     * `robots_txt_content` sama sekali.
     */
    private const DEFAULT_ROBOTS_CONTENT = "User-agent: *\nDisallow:\n\nSitemap: {site_url}/sitemap.xml\n";

    /**
     * `sitemap.xml` — dihitung ulang setiap request dari data terkini
     * (bukan file statis), memakai persis aturan visibilitas controller
     * publik masing-masing modul (AMC-224 research.md §3). Komposisi
     * jenis konten mengikuti sakelar SeoSettings (FR-038, spec
     * 023-site-settings).
     */
    public function xml(): Response
    {
        $seo = app(SeoSettings::class);

        abort_unless($seo->sitemap_enabled, 404);

        $staticUrls = [
            ['loc' => url('/'), 'lastmod' => null],
            ['loc' => url('/tentang-kami'), 'lastmod' => null],
            ['loc' => url('/kontak'), 'lastmod' => null],
            ['loc' => url('/faq'), 'lastmod' => null],
            ['loc' => url('/artikel'), 'lastmod' => null],
            ['loc' => url('/produk'), 'lastmod' => null],
            ['loc' => url('/portfolio'), 'lastmod' => null],
        ];

        if (app(SiteSettings::class)->career_module_enabled) {
            $staticUrls[] = ['loc' => url('/karir'), 'lastmod' => null];
        }

        $items = collect();

        if ($seo->sitemap_include_products) {
            $items = $items->concat(Product::all()->map(fn (Product $product) => [
                'loc' => url('/produk/'.$product->slug),
                'lastmod' => $product->updated_at,
            ]));
        }

        if ($seo->sitemap_include_articles) {
            $items = $items->concat(
                Article::query()
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->get()
                    ->map(fn (Article $article) => [
                        'loc' => url('/artikel/'.$article->slug),
                        'lastmod' => $article->updated_at,
                    ])
            );
        }

        if ($seo->sitemap_include_pages) {
            $items = $items->concat(CustomPage::all()->map(fn (CustomPage $page) => [
                'loc' => url('/halaman/'.$page->slug),
                'lastmod' => $page->updated_at,
            ]));
        }

        if ($seo->sitemap_include_portfolio) {
            $items = $items->concat(
                PortfolioProject::query()
                    ->where('is_active', true)
                    ->get()
                    ->map(fn (PortfolioProject $project) => [
                        'loc' => url('/portfolio/'.$project->slug),
                        'lastmod' => $project->updated_at,
                    ])
            );
        }

        return response()
            ->view('sitemap', [
                'staticUrls' => $staticUrls,
                'items' => $items,
                'changefreq' => $seo->sitemap_changefreq,
                'priority' => $seo->sitemap_priority,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * `robots.txt` dinamis — isinya berasal dari SeoSettings (FR-035),
     * dengan penanda {site_url} tergantikan alamat aktif (FR-036) dan
     * baris `Sitemap:` dibuang bila peta situs sedang dimatikan (FR-040,
     * spec 023-site-settings).
     */
    public function robots(): Response
    {
        $seo = app(SeoSettings::class);

        $content = filled($seo->robots_txt_content)
            ? $seo->robots_txt_content
            : self::DEFAULT_ROBOTS_CONTENT;

        $content = str_replace('{site_url}', url(''), $content);

        if (! $seo->sitemap_enabled) {
            $content = collect(explode("\n", $content))
                ->reject(fn (string $line) => str_starts_with(trim($line), 'Sitemap:'))
                ->implode("\n");
        }

        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
