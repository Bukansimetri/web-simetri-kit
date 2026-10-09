<?php

namespace App\Filament\Resources\ContactTopicResource\Pages;

use App\Filament\Resources\ContactTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageContactTopics extends ManageRecords
{
    protected static string $resource = ContactTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
