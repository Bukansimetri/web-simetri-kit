<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __invoke(): View
    {
        $faqItems = FaqItem::query()->orderBy('order')->get();

        return view('pages.faq', ['faqItems' => $faqItems]);
    }
}
