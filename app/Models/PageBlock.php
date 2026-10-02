<?php

namespace App\Models;

use App\Enums\PageBlockType;
use Database\Factories\PageBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageBlock extends Model
{
    /** @use HasFactory<PageBlockFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'block',
        'data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'block' => PageBlockType::class,
            'data' => 'array',
        ];
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
