<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\AboutMissionResource\Pages;
use App\Filament\Support\SectionItemResource;

class AboutMissionResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $modelLabel = 'Poin Misi';

    protected static ?string $slug = 'tentang-kami-misi';

    public static function section(): PageSection
    {
        return PageSection::AboutMission;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutMissions::route('/'),
            'create' => Pages\CreateAboutMission::route('/create'),
            'edit' => Pages\EditAboutMission::route('/{record}/edit'),
        ];
    }
}
