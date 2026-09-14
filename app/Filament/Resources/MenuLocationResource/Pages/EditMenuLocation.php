<?php

namespace App\Filament\Resources\MenuLocationResource\Pages;

use App\Filament\Resources\MenuLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenuLocation extends EditRecord
{
    protected static string $resource = MenuLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
