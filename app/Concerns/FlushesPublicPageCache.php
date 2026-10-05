<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Dipasang pada model yang tampil di halaman publik: setiap simpan/hapus
 * menaikkan versi cache halaman publik agar perubahan admin langsung tampil
 * di semua variasi halaman. Event `updated` sengaja tidak dipakai agar
 * `incrementQuietly` (mis. view_count) tidak membuang cache.
 */
trait FlushesPublicPageCache
{
    protected static function bootFlushesPublicPageCache(): void
    {
        $bump = static fn (Model $model) => self::bumpPublicPageVersionFromModel();

        static::saved($bump);
        static::deleted($bump);
    }

    private static function bumpPublicPageVersionFromModel(): void
    {
        (new class
        {
            use CachesPublicPages;
        })::bumpPublicPageVersion();
    }
}
