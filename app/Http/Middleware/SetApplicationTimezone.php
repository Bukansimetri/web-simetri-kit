<?php

namespace App\Http\Middleware;

use App\Settings\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menerapkan zona waktu dari SiteSettings sebagai acuan tampilan tanggal
 * pada halaman publik (FR-005). Dipasang pada grup middleware `web` saja —
 * panel admin membangun middleware stack sendiri (lihat AdminPanelProvider)
 * dan sengaja tidak tersentuh perubahan ini.
 */
class SetApplicationTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = app(SiteSettings::class)->timezone;

        date_default_timezone_set($timezone);
        config(['app.timezone' => $timezone]);

        return $next($request);
    }
}
