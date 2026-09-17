<?php

namespace App\Filament\Resources\ElectricityApplianceResource\Pages;

use App\Filament\Resources\ElectricityApplianceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageElectricityAppliances extends ManageRecords
{
    protected static string $resource = ElectricityApplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
