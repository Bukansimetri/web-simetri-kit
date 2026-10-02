<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\WhyChooseItemResource\Pages;
use App\Filament\Support\SectionItemResource;

class WhyChooseItemResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $modelLabel = 'Kartu Alasan';

    protected static ?string $slug = 'beranda-mengapa-beralih';

    public static function section(): PageSection
    {
        return PageSection::WhyChoose;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhyChooseItems::route('/'),
            'create' => Pages\CreateWhyChooseItem::route('/create'),
            'edit' => Pages\EditWhyChooseItem::route('/{record}/edit'),
        ];
    }
}
