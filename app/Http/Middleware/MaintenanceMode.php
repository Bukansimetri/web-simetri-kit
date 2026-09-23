<?php

namespace App\Http\Middleware;

use App\Settings\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menutup halaman publik dengan halaman pemeliharaan saat SiteSettings
 * mengaktifkannya (FR-010 sampai FR-014). Pengguna yang sudah login tetap
 * melihat isi situs sebenarnya (FR-013) — beda dari `php artisan down`
 * bawaan Laravel yang mematikan seluruh aplikasi termasuk panel admin dan
 * tidak bisa dinyalakan admin non-teknis dari panel (research.md R5).
 *
 * Dipasang pada grup middleware `web` saja — panel admin membangun
 * middleware stack sendiri dan tidak pernah melewati middleware ini
 * (lihat AdminPanelProvider, sama seperti SetApplicationTimezone).
 */
class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(SiteSettings::class)->maintenance_mode || auth()->check()) {
            return $next($request);
        }

        $response = response()->view('maintenance', [], 503);
        $response->headers->set('Retry-After', 3600);

        return $response;
    }
}
