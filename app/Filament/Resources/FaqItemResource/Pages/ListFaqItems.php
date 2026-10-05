<?php

namespace App\Filament\Resources\FaqItemResource\Pages;

use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\FaqItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaqItems extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::ProductFaq,
            PublicSection::ContactFaq,
        ];
    }

    protected static string $resource = FaqItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
