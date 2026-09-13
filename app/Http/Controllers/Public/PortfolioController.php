<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    use CachesPublicPages;

    public function index(Request $request): View
    {
        $activeSlug = $request->query('kategori');

        // Key per-kategori WAJIB (research.md §3) — filter kategori di sini
        // server-side (beda dari Produk/Artikel yang client-side), jadi key
        // tanpa parameter ini akan membocorkan hasil filter antar kategori.
        $data = $this->rememberPublicPage(
            'public-page:portfolio.index:'.($activeSlug ?: 'all'),
            function () use ($activeSlug) {
                $categories = PortfolioCategory::orderBy('order')->get();
                $activeCategory = $activeSlug ? $categories->firstWhere('slug', $activeSlug) : null;

                $projects = PortfolioProject::query()
                    ->where('is_active', true)
                    ->with('portfolioCategory')
                    ->when($activeCategory, fn ($query) => $query->where('portfolio_category_id', $activeCategory->id))
                    ->orderBy('order')
                    ->orderBy('id')
                    ->get();

                return [
                    'projects' => $projects,
                    'categories' => $categories,
                    'activeSlug' => $activeCategory?->slug,
                ];
            }
        );

        return view('pages.portfolio.index', $data);
    }

    public function show(PortfolioProject $portfolioProject): View
    {
        abort_unless($portfolioProject->is_active, 404);

        // $portfolioProject SELALU fresh via route-model binding
        // (research.md §3) — hanya relasi turunannya yang dibungkus cache,
        // supaya model utama tidak pernah basi.
        $portfolioCategory = $this->rememberPublicPage(
            "public-page:portfolio.show:{$portfolioProject->slug}",
            fn () => $portfolioProject->portfolioCategory
        );
        $portfolioProject->setRelation('portfolioCategory', $portfolioCategory);

        return view('pages.portfolio.show', ['project' => $portfolioProject]);
    }
}
