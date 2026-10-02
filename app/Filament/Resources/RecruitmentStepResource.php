<?php

namespace App\Filament\Resources;

use App\Enums\PageSection;
use App\Filament\Resources\RecruitmentStepResource\Pages;
use App\Filament\Support\SectionItemResource;

class RecruitmentStepResource extends SectionItemResource
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'Langkah';

    protected static ?string $slug = 'karir-proses-rekrutmen';

    public static function section(): PageSection
    {
        return PageSection::RecruitmentProcess;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecruitmentSteps::route('/'),
            'create' => Pages\CreateRecruitmentStep::route('/create'),
            'edit' => Pages\EditRecruitmentStep::route('/{record}/edit'),
        ];
    }
}
