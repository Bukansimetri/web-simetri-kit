<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Katalog peralatan listrik rumah tangga yang dipakai kalkulator "Berdasarkan
 * Peralatan" (App\Services\SavingsEstimator). Watt tiap peralatan diambil
 * dari sini di server — client hanya mengirim `slug` + qty, sehingga nilainya
 * tidak bisa dipalsukan dari browser.
 */
class ElectricityAppliance extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'electricity-appliances:active';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'watt',
        'order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'watt' => 'integer',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Katalog aktif, di-cache karena dipakai di tiap render halaman kalkulator
     * & tiap submit lead. Disimpan sebagai array atribut mentah (bukan
     * instance Eloquent) supaya cache tidak rusak saat class ini di-reload
     * ulang (mis. composer dump-autoload / restart dev server) — unserialize
     * objek PHP gagal kalau definisi class berubah sejak nilai di-cache,
     * sedangkan array + hydrate() selalu aman.
     *
     * @return Collection<int, self>
     */
    public static function activeCatalog(): Collection
    {
        $rows = Cache::rememberForever(
            self::CACHE_KEY,
            fn () => self::query()->active()->ordered()->get()->map->getAttributes()->all()
        );

        return self::hydrate($rows);
    }
}
