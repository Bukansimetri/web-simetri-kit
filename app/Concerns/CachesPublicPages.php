<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Caching sementara (short-TTL) untuk controller publik read-only
 * (AMC-225 FR-005/FR-007). TIDAK dipakai model/repository — hanya
 * controller publik — supaya panel admin (Filament) TIDAK PERNAH
 * terpengaruh (FR-008, lihat research.md §3).
 *
 * Setiap kunci diberi nomor versi global. Model publik menaikkan nomor itu
 * saat disimpan/dihapus (lihat FlushesPublicPageCache), sehingga semua variasi
 * halaman (filter, pencarian, paginasi) langsung segar tanpa Cache::flush().
 */
trait CachesPublicPages
{
    private const CACHE_TTL = 300; // 5 menit — research.md §3

    protected const PUBLIC_PAGE_VERSION_KEY = 'public-page:version';

    protected function rememberPublicPage(string $key, \Closure $callback)
    {
        return Cache::remember($key.':v'.static::publicPageVersion(), self::CACHE_TTL, $callback);
    }

    public static function publicPageVersion(): int
    {
        return (int) Cache::get(self::PUBLIC_PAGE_VERSION_KEY, 1);
    }

    public static function bumpPublicPageVersion(): void
    {
        Cache::forever(self::PUBLIC_PAGE_VERSION_KEY, static::publicPageVersion() + 1);
    }
}
