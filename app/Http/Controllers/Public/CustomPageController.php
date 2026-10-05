<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use Illuminate\View\View;

class CustomPageController extends Controller
{
    public function __invoke(CustomPage $customPage): View
    {
        $view = $customPage->isLegal() ? 'pages.custom-page.legal' : 'pages.custom-page.show';

        return view($view, ['customPage' => $customPage]);
    }
}
