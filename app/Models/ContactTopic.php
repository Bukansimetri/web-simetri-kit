<?php

namespace App\Models;

use App\Concerns\FlushesPublicPageCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pilihan "Topik Kebutuhan" di form Kontak. `slug` yang disimpan ke
 * `contact_submissions.topic`, sehingga submission lama tetap utuh walau
 * topiknya kemudian diganti nama atau dihapus.
 */
class ContactTopic extends Model
{
    use FlushesPublicPageCache;
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
