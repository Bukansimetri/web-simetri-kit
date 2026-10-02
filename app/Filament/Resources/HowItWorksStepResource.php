<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\HowItWorksStepResource\Pages;
use App\Filament\Support\SectionItemResource;

class HowItWorksStepResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $modelLabel = 'Langkah';

    protected static ?string $slug = 'beranda-cara-kerja';

    public static function section(): PageSection
    {
        return PageSection::HowItWorks;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHowItWorksSteps::route('/'),
            'create' => Pages\CreateHowItWorksStep::route('/create'),
            'edit' => Pages\EditHowItWorksStep::route('/{record}/edit'),
        ];
    }
}
