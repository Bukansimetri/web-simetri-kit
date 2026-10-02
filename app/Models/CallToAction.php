<?php

namespace App\Models;

use App\Enums\CtaPlacement;
use Database\Factories\CallToActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallToAction extends Model
{
    /** @use HasFactory<CallToActionFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'placement',
        'title',
        'body',
        'primary_label',
        'secondary_label',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placement' => CtaPlacement::class,
        ];
    }
}
