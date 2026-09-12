<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Seo\JsonLd;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        $articles = Article::query()
            ->with('articleCategory')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->get();

        return view('pages.artikel.index', [
            'featured' => $articles->first(),
            'articles' => $articles->skip(1)->values(),
            'categories' => ArticleCategory::query()->orderBy('order')->get(),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->load('articleCategory', 'tags');

        $related = Article::query()
            ->with('articleCategory')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $article->id)
            ->where('article_category_id', $article->article_category_id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('pages.artikel.show', [
            'article' => $article,
            'related' => $related,
            'schema' => JsonLd::article($article),
        ]);
    }
}
