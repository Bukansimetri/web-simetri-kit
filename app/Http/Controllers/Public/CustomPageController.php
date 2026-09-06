<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use Illuminate\View\View;

class CustomPageController extends Controller
{
    public function __invoke(CustomPage $customPage): View
    {
        return view('pages.custom-page.show', ['customPage' => $customPage]);
    }
}
