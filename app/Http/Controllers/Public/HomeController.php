<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::query()
            ->orderBy('order')
            ->take(3)
            ->get();

        $banners = Banner::live()->get();

        return view('pages.home', [
            'products' => $products,
            'banners' => $banners,
        ]);
    }
}
