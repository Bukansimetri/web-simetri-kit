<?php

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\TestimonialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestimonials extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::HomeTestimonials,
            PublicSection::AboutTestimonials,
        ];
    }

    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
