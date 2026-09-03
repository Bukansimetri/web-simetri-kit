<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    /**
     * Default kosong untuk kolom json — supaya form Filament yang belum
     * menyertakan field ini (mis. create dasar di US2, sebelum galeri/specs/
     * fitur ditambahkan di US3/US4) tetap bisa menyimpan tanpa error NOT NULL.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'images' => '[]',
        'specs' => '[]',
        'features' => '[]',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'category_id',
        'short_description',
        'description',
        'price',
        'strikethrough_price',
        'images',
        'specs',
        'features',
        'order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'strikethrough_price' => 'decimal:2',
            'images' => 'array',
            'specs' => 'array',
            'features' => 'array',
            'order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * URL gambar sampul (cover) — gambar pertama dalam urutan galeri (FR-011).
     * Null kalau produk belum punya gambar sama sekali (edge case placeholder,
     * ditangani di Blade — lihat FR-018).
     */
    public function coverImageUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    /**
     * Seluruh URL galeri gambar, urut sesuai `images` (FR-010).
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path) => Storage::disk('public')->url($path))
            ->all();
    }
}
