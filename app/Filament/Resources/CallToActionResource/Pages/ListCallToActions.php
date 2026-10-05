<?php

namespace App\Filament\Resources\CallToActionResource\Pages;

use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\CallToActionResource;
use Filament\Resources\Pages\ListRecords;

class ListCallToActions extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::HomeCta,
            PublicSection::ProductCtaCalculator,
            PublicSection::ProductCtaClosing,
            PublicSection::ProductDetailCta,
            PublicSection::ArticleCta,
            PublicSection::ArticleDetailCta,
            PublicSection::AboutCta,
            PublicSection::FaqCta,
            PublicSection::CareerCta,
        ];
    }

    protected static string $resource = CallToActionResource::class;
}
