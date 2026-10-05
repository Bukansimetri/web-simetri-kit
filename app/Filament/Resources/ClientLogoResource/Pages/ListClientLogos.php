<?php

namespace App\Filament\Resources\ClientLogoResource\Pages;

use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\ClientLogoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientLogos extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::AboutClientLogos,
        ];
    }

    protected static string $resource = ClientLogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
