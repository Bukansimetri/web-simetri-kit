<?php

namespace App\Filament\Resources\PageBlockResource\Pages;

use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\PageBlockResource;
use Filament\Resources\Pages\ListRecords;

class ListPageBlocks extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::AboutWhoWeAre,
            PublicSection::AboutVision,
        ];
    }

    protected static string $resource = PageBlockResource::class;
}
