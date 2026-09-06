<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use App\Models\Testimonial;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $clientLogos = ClientLogo::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('pages.tentang-kami', [
            'testimonials' => $testimonials,
            'clientLogos' => $clientLogos,
        ]);
    }
}
