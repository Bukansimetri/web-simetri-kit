<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use App\Support\Seo\JsonLd;
use Illuminate\View\View;

class FaqController extends Controller
{
    use CachesPublicPages;

    public function __invoke(): View
    {
        $faqItems = $this->rememberPublicPage(
            'public-page:faq',
            fn () => FaqItem::query()->orderBy('order')->get()
        );

        return view('pages.faq', [
            'faqItems' => $faqItems,
            'categories' => $faqItems->pluck('category')->filter()->unique()->values(),
            'schema' => JsonLd::faqPage($faqItems),
        ]);
    }
}
