<?php

namespace App\Models;

use App\Concerns\FlushesPublicPageCache;
use App\Enums\FaqPlacement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    use FlushesPublicPageCache;
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'question',
        'answer',
        'category',
        'placement',
        'is_active',
        'order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'placement' => FaqPlacement::class,
            'is_active' => 'boolean',
        ];
    }

    public function scopeForPlacement(Builder $query, FaqPlacement $placement): void
    {
        $query->where('placement', $placement->value);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
