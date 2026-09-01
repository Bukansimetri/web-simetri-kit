<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->orderBy('order')->get();

        return view('pages.produk.index', ['products' => $products]);
    }

    public function show(Product $product): View
    {
        $relatedProducts = Product::query()
            ->where('category', $product->category)
            ->whereKeyNot($product->getKey())
            ->orderBy('order')
            ->take(3)
            ->get();

        return view('pages.produk.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
