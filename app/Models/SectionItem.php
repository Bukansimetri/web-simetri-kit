<?php

namespace App\Models;

use App\Enums\PageSection;
use Database\Factories\SectionItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionItem extends Model
{
    /** @use HasFactory<SectionItemFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'section',
        'icon',
        'title',
        'description',
        'is_active',
        'is_emphasized',
        'order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section' => PageSection::class,
            'is_active' => 'boolean',
            'is_emphasized' => 'boolean',
            'order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (SectionItem $item): void {
            if (! $item->section->supportsEmphasis()) {
                $item->is_emphasized = false;

                return;
            }

            if ($item->is_emphasized && $item->isDirty('is_emphasized')) {
                static::query()
                    ->where('section', $item->section->value)
                    ->when($item->exists, fn (Builder $query) => $query->whereKeyNot($item->getKey()))
                    ->update(['is_emphasized' => false]);
            }
        });
    }

    public function scopeForSection(Builder $query, PageSection $section): Builder
    {
        return $query->where('section', $section->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
