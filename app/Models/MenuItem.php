<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    use HasFactory;

    public const LINK_TYPE_INTERNAL = 'internal';

    public const LINK_TYPE_EXTERNAL = 'external';

    public const LINK_TYPE_NONE = 'none';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_location_id',
        'parent_id',
        'label',
        'link_type',
        'linkable_type',
        'linkable_id',
        'external_url',
        'open_in_new_tab',
        'order_column',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'order_column' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function menuLocation(): BelongsTo
    {
        return $this->belongsTo(MenuLocation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order_column')->orderBy('id');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Resolusi URL final item menu (kontrak: contracts/menu-rendering-contract.md §2).
     * Tautan internal dihitung ulang lewat model target agar tetap valid saat
     * slug berubah (FR-008); mengembalikan null bila target sudah tidak ada
     * atau tidak lagi punya representasi publik (FR-011), tanpa melempar error.
     */
    public function resolveUrl(): ?string
    {
        return match ($this->link_type) {
            self::LINK_TYPE_EXTERNAL => $this->external_url,
            self::LINK_TYPE_INTERNAL => $this->linkable?->getPublicUrl(),
            default => null,
        };
    }

    /**
     * Bentuk array yang dipakai kontrak render (contracts/menu-rendering-contract.md §1):
     * label, href (null bila tanpa tautan/tautan rusak), target, dan children.
     *
     * @return array{label: string, href: ?string, target: string, children: array}
     */
    public function toRenderableArray(): array
    {
        return [
            'label' => $this->label,
            'href' => $this->resolveUrl(),
            'target' => $this->open_in_new_tab ? '_blank' : '_self',
            'children' => $this->relationLoaded('children')
                ? $this->children->map(fn (self $child) => $child->toRenderableArray())->all()
                : [],
        ];
    }

    /**
     * Item aktif untuk satu lokasi menu, terurut & dalam bentuk renderable
     * array (dipakai <x-layout.menu> dan integrasi header/footer). Aman
     * dipanggil meski lokasi belum ada — mengembalikan koleksi kosong.
     */
    public static function treeForLocation(string $locationSlug): Collection
    {
        return static::query()
            ->whereHas('menuLocation', fn (Builder $query) => $query->where('slug', $locationSlug))
            ->active()
            ->root()
            ->with(['linkable', 'children' => fn ($query) => $query->active()->with('linkable')])
            ->orderBy('order_column')
            ->orderBy('id')
            ->get()
            ->map(fn (self $item) => $item->toRenderableArray());
    }
}
