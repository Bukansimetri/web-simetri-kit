<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\AboutTrustResource\Pages;
use App\Filament\Support\SectionItemResource;

class AboutTrustResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $modelLabel = 'Angka Kepercayaan';

    protected static ?string $slug = 'tentang-kami-trust-strip';

    public static function section(): PageSection
    {
        return PageSection::AboutTrust;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutTrusts::route('/'),
            'create' => Pages\CreateAboutTrust::route('/create'),
            'edit' => Pages\EditAboutTrust::route('/{record}/edit'),
        ];
    }
}
