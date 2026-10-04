<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Concerns\CachesPublicPages;
use App\Filament\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('artikel.preview', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ArticleResource::mutatePublishStatus($data);
    }

    protected function afterSave(): void
    {
        $this->record->syncTags($this->data['tags'] ?? []);

        // Tag disinkronkan setelah event `saved`, jadi versi cache dinaikkan lagi di sini.
        (new class
        {
            use CachesPublicPages;
        })::bumpPublicPageVersion();
    }
}
