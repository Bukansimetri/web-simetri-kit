<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
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
}
