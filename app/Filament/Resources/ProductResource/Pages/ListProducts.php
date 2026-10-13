<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\PublicSection;
use App\Models\SectionHeading;
use App\Support\PageContent\DefaultPageContent;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [
            PublicSection::HomeSolutions,
        ];
    }

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('editHomeSolutionsHeading')
                ->label('Ubah Judul Section Beranda')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->modalHeading('Judul Section Solusi Produk Beranda')
                ->fillForm(function (): array {
                    $defaults = DefaultPageContent::forCurrentSite(DefaultPageContent::headings()['beranda.solusi']);
                    $heading = SectionHeading::query()->where('section', 'beranda.solusi')->first();

                    return [
                        'title' => $heading?->title ?? $defaults['title'],
                        'subtitle' => $heading?->subtitle ?? $defaults['subtitle'],
                    ];
                })
                ->form([
                    Textarea::make('title')->label('Judul')->required()->maxLength(80)->rows(2),
                    Textarea::make('subtitle')->label('Subjudul')->maxLength(250)->rows(2),
                ])
                ->action(function (array $data): void {
                    SectionHeading::query()->updateOrCreate(
                        ['section' => 'beranda.solusi'],
                        ['title' => $data['title'], 'subtitle' => $data['subtitle'] ?? null],
                    );

                    Notification::make()->success()->title('Judul section disimpan.')->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
