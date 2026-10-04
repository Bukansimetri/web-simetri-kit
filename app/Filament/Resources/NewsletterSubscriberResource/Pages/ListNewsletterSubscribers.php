<?php

namespace App\Filament\Resources\NewsletterSubscriberResource\Pages;

use App\Filament\Resources\NewsletterSubscriberResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    /**
     * Tidak ada CreateAction — pendaftar masuk lewat formulir "Update Mingguan" di halaman Artikel.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
