<?php

namespace App\Models;

use App\Enums\PageSection;
use Database\Factories\SectionHeadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionHeading extends Model
{
    /** @use HasFactory<SectionHeadingFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'section',
        'title',
        'subtitle',
        'eyebrow',
        'featured_image_path',
        'featured_icon',
        'featured_title',
        'featured_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section' => PageSection::class,
        ];
    }
}
