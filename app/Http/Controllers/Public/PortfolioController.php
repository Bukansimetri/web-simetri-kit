<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $categories = PortfolioCategory::orderBy('order')->get();

        $activeSlug = $request->query('kategori');
        $activeCategory = $activeSlug ? $categories->firstWhere('slug', $activeSlug) : null;

        $projects = PortfolioProject::query()
            ->where('is_active', true)
            ->with('portfolioCategory')
            ->when($activeCategory, fn ($query) => $query->where('portfolio_category_id', $activeCategory->id))
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('pages.portfolio.index', [
            'projects' => $projects,
            'categories' => $categories,
            'activeSlug' => $activeCategory?->slug,
        ]);
    }

    public function show(PortfolioProject $portfolioProject): View
    {
        abort_unless($portfolioProject->is_active, 404);

        $portfolioProject->load('portfolioCategory');

        return view('pages.portfolio.show', ['project' => $portfolioProject]);
    }
}
