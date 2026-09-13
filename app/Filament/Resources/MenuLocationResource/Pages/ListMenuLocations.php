<?php

namespace App\Filament\Resources\MenuLocationResource\Pages;

use App\Filament\Resources\MenuLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuLocations extends ListRecords
{
    protected static string $resource = MenuLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
