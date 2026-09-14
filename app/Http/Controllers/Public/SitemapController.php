<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CustomPage;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Settings\BrandSettings;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * `sitemap.xml` — dihitung ulang setiap request dari data terkini
     * (bukan file statis), memakai persis aturan visibilitas controller
     * publik masing-masing modul (AMC-224 research.md §3).
     */
    public function xml(): Response
    {
        $staticUrls = [
            ['loc' => url('/'), 'lastmod' => null],
            ['loc' => url('/tentang-kami'), 'lastmod' => null],
            ['loc' => url('/kontak'), 'lastmod' => null],
            ['loc' => url('/faq'), 'lastmod' => null],
            ['loc' => url('/artikel'), 'lastmod' => null],
            ['loc' => url('/produk'), 'lastmod' => null],
            ['loc' => url('/portfolio'), 'lastmod' => null],
        ];

        if (app(BrandSettings::class)->career_module_enabled) {
            $staticUrls[] = ['loc' => url('/karir'), 'lastmod' => null];
        }

        $items = collect()
            ->concat(Product::all()->map(fn (Product $product) => [
                'loc' => url('/produk/'.$product->slug),
                'lastmod' => $product->updated_at,
            ]))
            ->concat(
                Article::query()
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->get()
                    ->map(fn (Article $article) => [
                        'loc' => url('/artikel/'.$article->slug),
                        'lastmod' => $article->updated_at,
                    ])
            )
            ->concat(CustomPage::all()->map(fn (CustomPage $page) => [
                'loc' => url('/halaman/'.$page->slug),
                'lastmod' => $page->updated_at,
            ]))
            ->concat(
                PortfolioProject::query()
                    ->where('is_active', true)
                    ->get()
                    ->map(fn (PortfolioProject $project) => [
                        'loc' => url('/portfolio/'.$project->slug),
                        'lastmod' => $project->updated_at,
                    ])
            );

        return response()
            ->view('sitemap', ['staticUrls' => $staticUrls, 'items' => $items])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * `robots.txt` dinamis — menggantikan `public/robots.txt` statis
     * (dihapus, lihat research.md §2). Aturan `User-agent`/`Disallow`
     * identik dengan file lama; baris `Sitemap:` mengikuti domain aktif.
     */
    public function robots(): Response
    {
        $content = "User-agent: *\nDisallow:\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
