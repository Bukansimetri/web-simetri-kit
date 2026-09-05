<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use App\Settings\BrandSettings;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(app(BrandSettings::class)->career_module_enabled, 404);

        $jobOpenings = JobOpening::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('pages.karir', ['jobOpenings' => $jobOpenings]);
    }
}
