<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function __invoke(): View
    {
        $jobOpenings = JobOpening::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('pages.karir', ['jobOpenings' => $jobOpenings]);
    }
}
