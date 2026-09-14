<?php

namespace App\Filament\Resources\CalculatorLeadResource\Pages;

use App\Filament\Resources\CalculatorLeadResource;
use App\Models\CalculatorLead;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCalculatorLead extends EditRecord
{
    protected static string $resource = CalculatorLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Bila admin mengubah status keluar dari "Baru" lewat form edit (bukan
     * lewat aksi cepat "Tandai Sudah Dihubungi" di tabel) dan belum ada PIC
     * tercatat, catat otomatis siapa & kapan — supaya jejak follow-up tetap
     * terisi walau admin mengedit langsung dari sini.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) !== CalculatorLead::STATUS_NEW && blank($this->record->followed_up_at)) {
            $data['followed_up_by_id'] = Auth::id();
            $data['followed_up_at'] = now();
        }

        return $data;
    }
}
