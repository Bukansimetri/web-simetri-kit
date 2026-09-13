<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    use CachesPublicPages;

    public function __invoke(): View
    {
        $data = $this->rememberPublicPage('public-page:home', function () {
            $products = Product::query()
                ->orderBy('order')
                ->take(3)
                ->get();

            $banners = Banner::live()->get();

            $testimonials = Testimonial::query()
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->take(3)
                ->get();

            return compact('products', 'banners', 'testimonials');
        });

        return view('pages.home', $data);
    }
}
