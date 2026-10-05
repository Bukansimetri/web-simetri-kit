<?php

namespace App\Models;

use App\Concerns\FlushesPublicPageCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobOpening extends Model
{
    use FlushesPublicPageCache;
    use HasFactory;

    /**
     * Daftar tipe pekerjaan tetap (bukan taxonomy entity terpisah) — dipakai
     * sebagai `options()` Select di form Filament (FR-007, research.md §1).
     *
     * @var array<string, string>
     */
    public const EMPLOYMENT_TYPES = [
        'full-time' => 'Full-time',
        'part-time' => 'Part-time',
        'contract' => 'Kontrak',
        'internship' => 'Magang',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'location',
        'employment_type',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Ringkasan teks polos dari deskripsi berformat: antar blok dipisah spasi,
     * tanpa tag, untuk kartu lowongan dan deskripsi meta.
     */
    public function descriptionExcerpt(?int $limit = null): string
    {
        $spaced = preg_replace('#</(p|li|h[1-6]|blockquote|div)>|<br\s*/?>#i', ' ', (string) $this->description);
        $text = html_entity_decode(strip_tags((string) $spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $text));

        return $limit === null ? $text : Str::limit($text, $limit);
    }
}
