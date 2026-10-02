<?php

namespace App\Filament\Support\Pages;

use App\Models\SectionItem;
use Filament\Resources\Pages\CreateRecord;

abstract class CreateSectionItem extends CreateRecord
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $section = static::getResource()::section();

        $data['section'] = $section;
        $data['order'] = (int) SectionItem::query()->forSection($section)->max('order') + 1;

        return $data;
    }
}
