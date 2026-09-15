<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Reorder drag-and-drop Filament memakai mass-update lewat query
     * builder (Filament\Tables\Concerns\CanReorderRecords::reorderTable()),
     * BUKAN Eloquent save() per baris — sehingga event `saved` yang
     * dipasang Banner::booted() tidak ikut terpicu. Buang cache secara
     * eksplisit di sini supaya urutan baru langsung terlihat di beranda,
     * konsisten dengan FR-018 (022-banner-hero-slider).
     *
     * @param  array<int, int|string>  $order
     */
    public function reorderTable(array $order): void
    {
        parent::reorderTable($order);

        Cache::forget('public-page:home');
    }
}
