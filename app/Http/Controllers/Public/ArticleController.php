<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Seo\JsonLd;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Tags\Tag;

class ArticleController extends Controller
{
    use CachesPublicPages;

    /**
     * Jumlah artikel per kelompok "Muat lebih banyak".
     */
    public const PER_PAGE = 6;

    private const MAX_PAGE = 50;

    private const POPULAR_TAG_LIMIT = 10;

    public function index(Request $request): View
    {
        $search = $this->searchTerm($request);
        $categoryId = $this->intParam($request, 'kategori');
        $tagSlug = $this->slugParam($request, 'tag');
        $page = min(max($this->intParam($request, 'halaman'), 1), self::MAX_PAGE);

        $data = $this->rememberPublicPage(
            'public-page:artikel.index:'.md5(json_encode([$search, $categoryId, $tagSlug, $page])),
            function () use ($search, $categoryId, $tagSlug, $page) {
                $categories = ArticleCategory::query()->orderBy('order')->get();
                $activeCategory = $categoryId ? $categories->firstWhere('id', $categoryId) : null;
                $activeTag = $tagSlug !== '' ? $this->findTag($tagSlug) : null;

                $limit = self::PER_PAGE * $page;

                $rows = $this->publishedQuery()
                    ->with('articleCategory')
                    ->when($search !== '', function (Builder $query) use ($search) {
                        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';

                        $query->where(fn ($inner) => $inner
                            ->whereRaw("title like ? escape '!'", [$like])
                            ->orWhereRaw("excerpt like ? escape '!'", [$like]));
                    })
                    ->when($activeCategory, fn (Builder $query) => $query->where('article_category_id', $activeCategory->id))
                    ->when($activeTag, fn (Builder $query) => $query->whereHas('tags', fn ($tags) => $tags->whereKey($activeTag->getKey())))
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit($limit + 1)
                    ->get();

                return [
                    'articles' => $rows->take($limit)->values(),
                    'hasMore' => $rows->count() > $limit,
                    'categories' => $categories,
                    'activeCategory' => $activeCategory,
                    'activeTag' => $activeTag,
                    'popularTags' => $this->popularTags(),
                ];
            }
        );

        return view('pages.artikel.index', [...$data, 'search' => $search, 'page' => $page]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->incrementQuietly('view_count');

        return $this->detailView($article, isPreview: false);
    }

    /**
     * Pratinjau untuk admin: boleh membuka draf/terjadwal, tidak menambah jumlah dilihat dan tidak diindeks.
     */
    public function preview(Request $request, Article $article): View|RedirectResponse
    {
        $panel = Filament::getPanel('admin');
        $user = $request->user();

        if ($user === null) {
            return redirect($panel->getLoginUrl());
        }

        abort_unless($user->canAccessPanel($panel), 403);

        return $this->detailView($article, isPreview: true);
    }

    private function detailView(Article $article, bool $isPreview): View
    {
        $article->load('articleCategory', 'tags', 'relatedProducts.category');

        // $article SELALU fresh via route-model binding (research.md §3).
        // Hanya query turunan ($related, $latest) yang dibungkus cache.
        $related = $this->rememberPublicPage(
            "public-page:artikel.show:{$article->slug}",
            fn () => $this->publishedQuery()
                ->with('articleCategory')
                ->where('id', '!=', $article->id)
                ->where('article_category_id', $article->article_category_id)
                ->orderByDesc('published_at')
                ->take(3)
                ->get()
        );

        $latest = $this->rememberPublicPage(
            "public-page:artikel.latest:{$article->id}",
            fn () => $this->publishedQuery()
                ->where('id', '!=', $article->id)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->take(5)
                ->get()
        );

        return view('pages.artikel.show', [
            'article' => $article,
            'related' => $related,
            'latest' => $latest,
            'isPreview' => $isPreview,
            'schema' => JsonLd::article($article),
        ]);
    }

    /**
     * @return Builder<Article>
     */
    private function publishedQuery(): Builder
    {
        return Article::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Tag yang paling banyak dipakai artikel terbit.
     *
     * @return Collection<int, Tag>
     */
    private function popularTags()
    {
        return Tag::query()
            ->select('tags.*', DB::raw('count(articles.id) as articles_count'))
            ->join('taggables', 'tags.id', '=', 'taggables.tag_id')
            ->join('articles', 'articles.id', '=', 'taggables.taggable_id')
            ->where('taggables.taggable_type', (new Article)->getMorphClass())
            ->whereNotNull('articles.published_at')
            ->where('articles.published_at', '<=', now())
            ->groupBy('tags.id')
            ->orderByDesc('articles_count')
            ->orderBy('tags.id')
            ->limit(self::POPULAR_TAG_LIMIT)
            ->get();
    }

    private function findTag(string $slug): ?Tag
    {
        return Tag::query()->where('slug->'.app()->getLocale(), $slug)->first();
    }

    /**
     * Kata kunci pencarian: dirapikan dan dibatasi 100 karakter.
     */
    private function searchTerm(Request $request): string
    {
        $term = $request->query('q');

        return is_string($term) ? Str::of($term)->squish()->limit(100, '')->toString() : '';
    }

    private function intParam(Request $request, string $key): int
    {
        $value = $request->query($key);

        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    private function slugParam(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? Str::limit(trim($value), 100, '') : '';
    }
}
