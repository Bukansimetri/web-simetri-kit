<?php

namespace App\Support\PageContent;

use App\Models\SectionItem;
use Illuminate\Support\Collection;

final readonly class SectionContent
{
    /**
     * @param  Collection<int, SectionItem>  $items
     * @param  array{image_path: ?string, icon: ?string, title: ?string, description: ?string}  $featured
     */
    public function __construct(
        public string $title,
        public ?string $subtitle,
        public Collection $items,
        public ?string $eyebrow = null,
        public array $featured = ['image_path' => null, 'icon' => null, 'title' => null, 'description' => null],
    ) {}
}
