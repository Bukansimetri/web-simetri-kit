<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\CareerValueResource\Pages;
use App\Filament\Support\SectionItemResource;

class CareerValueResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $modelLabel = 'Kartu Nilai';

    protected static ?string $slug = 'karir-mengapa-bergabung';

    public static function section(): PageSection
    {
        return PageSection::CareerValues;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCareerValues::route('/'),
            'create' => Pages\CreateCareerValue::route('/create'),
            'edit' => Pages\EditCareerValue::route('/{record}/edit'),
        ];
    }
}
