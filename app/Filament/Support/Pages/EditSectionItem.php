<?php

namespace App\Filament\Support\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

abstract class EditSectionItem extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
