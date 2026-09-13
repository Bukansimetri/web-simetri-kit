<?php

namespace App\Http\Controllers\Public;

use App\Concerns\CachesPublicPages;
use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\View\View;

class AboutController extends Controller
{
    use CachesPublicPages;

    public function __invoke(): View
    {
        $data = $this->rememberPublicPage('public-page:tentang-kami', function () {
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

            $teamMembers = TeamMember::query()
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            return compact('testimonials', 'clientLogos', 'teamMembers');
        });

        return view('pages.tentang-kami', $data);
    }
}
