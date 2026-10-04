<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Seo\JsonLd;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    use CachesPublicPages;

    public function index(Request $request): View
    {
        $search = $this->searchTerm($request);

        $data = $this->rememberPublicPage(
            'public-page:artikel.index:'.($search === '' ? 'all' : 'q-'.md5($search)),
            function () use ($search) {
                $articles = Article::query()
                    ->with('articleCategory')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->when($search !== '', function ($query) use ($search) {
                        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';

                        $query->where(fn ($inner) => $inner
                            ->whereRaw("title like ? escape '!'", [$like])
                            ->orWhereRaw("excerpt like ? escape '!'", [$like]));
                    })
                    ->orderByDesc('published_at')
                    ->get();

                return [
                    'articles' => $articles,
                    'categories' => ArticleCategory::query()->orderBy('order')->get(),
                ];
            }
        );

        return view('pages.artikel.index', [...$data, 'search' => $search]);
    }

    /**
     * Kata kunci pencarian: dirapikan dan dibatasi 100 karakter.
     */
    private function searchTerm(Request $request): string
    {
        $term = $request->query('q');

        return is_string($term) ? Str::of($term)->squish()->limit(100, '')->toString() : '';
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
