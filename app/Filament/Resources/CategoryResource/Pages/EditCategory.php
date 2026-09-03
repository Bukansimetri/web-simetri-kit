<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (Category $record, DeleteAction $action) {
                    if ($record->products()->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title('Kategori masih dipakai')
                            ->body('Kategori ini masih dipakai oleh satu atau lebih produk. Pindahkan atau hapus produk tsb terlebih dahulu.')
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
