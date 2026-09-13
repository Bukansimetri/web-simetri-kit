<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Caching sementara (short-TTL) untuk controller publik read-only
 * (AMC-225 FR-005/FR-007). TIDAK dipakai model/repository — hanya
 * controller publik — supaya panel admin (Filament) TIDAK PERNAH
 * terpengaruh (FR-008, lihat research.md §3).
 */
trait CachesPublicPages
{
    private const CACHE_TTL = 300; // 5 menit — research.md §3

    protected function rememberPublicPage(string $key, \Closure $callback)
    {
        return Cache::remember($key, self::CACHE_TTL, $callback);
    }
}
