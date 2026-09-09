<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'image_path',
        'alt_text',
        'link_url',
        'starts_at',
        'ends_at',
        'order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Banner yang sedang tayang: aktif DAN dalam periode tayang (inklusif).
     * Periode kosong = tanpa batas pada sisi tsb (FR-008, data-model.md).
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Status tayang turunan untuk kolom admin (FR-013). Konsisten dengan
     * scopeLive(): mengembalikan 'live' tepat ketika banner termasuk hasil
     * scopeLive().
     */
    public function displayStatus(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at !== null && $this->starts_at->gt(today())) {
            return 'scheduled';
        }

        if ($this->ends_at !== null && $this->ends_at->lt(today())) {
            return 'expired';
        }

        return 'live';
    }
}
