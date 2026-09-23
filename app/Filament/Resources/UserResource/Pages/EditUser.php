<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak boleh menghapus akun sendiri lewat sini — cara paling
            // mudah mengunci diri sendiri keluar dari panel tanpa sengaja.
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->id !== auth()->id()),
        ];
    }
}
