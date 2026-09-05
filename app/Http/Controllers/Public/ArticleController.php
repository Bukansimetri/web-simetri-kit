<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
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

        return view('pages.artikel.index', ['articles' => $articles]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->load('articleCategory', 'tags');

        return view('pages.artikel.show', ['article' => $article]);
    }
}
