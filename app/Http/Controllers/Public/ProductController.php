<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\Seo\JsonLd;
use Illuminate\View\View;

class ProductController extends Controller
{
    use CachesPublicPages;

    public function index(): View
    {
        $data = $this->rememberPublicPage('public-page:produk.index', function () {
            $products = Product::query()->orderBy('order')->get();
            $categories = Category::query()->orderBy('order')->get();

            return compact('products', 'categories');
        });

        return view('pages.produk.index', $data);
    }

    public function show(Product $product): View
    {
        // $product SELALU fresh via route-model binding, di luar cache
        // (research.md §3 — Do Things the Laravel Way, bukan diubah jadi
        // lookup manual demi bisa 100% ter-cache). Hanya query turunan
        // (relatedProducts) yang dibungkus cache.
        $relatedProducts = $this->rememberPublicPage(
            "public-page:produk.show:{$product->slug}",
            fn () => Product::query()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->getKey())
                ->orderBy('order')
                ->take(3)
                ->get()
        );

        return view('pages.produk.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'schema' => JsonLd::product($product),
        ]);
    }
}
