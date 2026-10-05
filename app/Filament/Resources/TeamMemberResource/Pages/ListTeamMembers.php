<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\TeamMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeamMembers extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::AboutTeam,
        ];
    }

    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
