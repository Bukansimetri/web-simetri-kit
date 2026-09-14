<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Seo\JsonLd;
use Illuminate\View\View;

class ArticleController extends Controller
{
    use CachesPublicPages;

    public function index(): View
    {
        $data = $this->rememberPublicPage('public-page:artikel.index', function () {
            $articles = Article::query()
                ->with('articleCategory')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->get();

            return [
                'featured' => $articles->first(),
                'articles' => $articles->skip(1)->values(),
                'categories' => ArticleCategory::query()->orderBy('order')->get(),
            ];
        });

        return view('pages.artikel.index', $data);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->load('articleCategory', 'tags');

        // $article SELALU fresh via route-model binding (research.md §3).
        // Hanya query turunan ($related) yang dibungkus cache.
        $related = $this->rememberPublicPage(
            "public-page:artikel.show:{$article->slug}",
            fn () => Article::query()
                ->with('articleCategory')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where('id', '!=', $article->id)
                ->where('article_category_id', $article->article_category_id)
                ->orderByDesc('published_at')
                ->take(3)
                ->get()
        );

        return view('pages.artikel.show', [
            'article' => $article,
            'related' => $related,
            'schema' => JsonLd::article($article),
        ]);
    }
}
