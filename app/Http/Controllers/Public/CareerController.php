<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use App\Settings\SiteSettings;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): View
    {
        abort_unless(app(SiteSettings::class)->career_module_enabled, 404);

        $jobOpenings = JobOpening::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('pages.karir', ['jobOpenings' => $jobOpenings]);
    }

    public function show(JobOpening $jobOpening): View
    {
        abort_unless(app(SiteSettings::class)->career_module_enabled && $jobOpening->is_active, 404);

        return view('pages.karir.show', ['job' => $jobOpening]);
    }
}
