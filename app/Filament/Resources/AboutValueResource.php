<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\AboutValueResource\Pages;
use App\Filament\Support\SectionItemResource;

class AboutValueResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $modelLabel = 'Kartu Nilai';

    protected static ?string $slug = 'tentang-kami-nilai';

    public static function section(): PageSection
    {
        return PageSection::AboutValues;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutValues::route('/'),
            'create' => Pages\CreateAboutValue::route('/create'),
            'edit' => Pages\EditAboutValue::route('/{record}/edit'),
        ];
    }
}
