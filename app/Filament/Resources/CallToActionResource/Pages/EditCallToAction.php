<?php

namespace App\Filament\Resources\CallToActionResource\Pages;

use App\Filament\Resources\CallToActionResource;
use Filament\Resources\Pages\EditRecord;

class EditCallToAction extends EditRecord
{
    protected static string $resource = CallToActionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['placement']);

        if (! $this->getRecord()->placement->hasSecondaryButton()) {
            $data['secondary_label'] = null;
        }

        return $data;
    }
}
