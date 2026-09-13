<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DemoSeedRecord extends Model
{
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'seedable_type',
        'seedable_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function seedable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Catat satu record sebagai bagian dari data demo (research.md #1, #4).
     * Aman dipanggil berulang untuk record yang sama (unique constraint).
     */
    public static function recordFor(Model $model): void
    {
        static::firstOrCreate([
            'seedable_type' => $model->getMorphClass(),
            'seedable_id' => $model->getKey(),
        ], [
            'created_at' => now(),
        ]);
    }

    /**
     * True bila model tsb sudah pernah di-seed sebagai konten demo —
     * dipakai seeder untuk tetap idempoten (research.md #4).
     */
    public static function alreadySeeded(string $modelClass): bool
    {
        return static::query()->where('seedable_type', (new $modelClass)->getMorphClass())->exists();
    }
}
