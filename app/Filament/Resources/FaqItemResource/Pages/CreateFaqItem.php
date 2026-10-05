<?php

namespace App\Filament\Resources\FaqItemResource\Pages;

use App\Filament\Resources\FaqItemResource;
use App\Models\FaqItem;
use Filament\Resources\Pages\CreateRecord;

class CreateFaqItem extends CreateRecord
{
    protected static string $resource = FaqItemResource::class;

    /**
     * Entri baru diletakkan di urutan terakhir pada tempat tampilnya.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['order'] = (int) FaqItem::query()->where('placement', $data['placement'])->max('order') + 1;

        if ($data['placement'] !== 'faq') {
            $data['category'] = null;
        }

        return $data;
    }
}
