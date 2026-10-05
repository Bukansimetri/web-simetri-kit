<?php

namespace App\Filament\Support\Pages;

use App\Enums\PageSection;
use App\Enums\PublicSection;
use App\Filament\Concerns\ShowsHiddenSectionNotice;
use App\Models\SectionHeading;
use App\Support\ImageUploads;
use App\Support\MaterialSymbolsIcons;
use App\Support\PageContent\DefaultPageContent;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

abstract class ListSectionItems extends ListRecords
{
    use ShowsHiddenSectionNotice;

    /**
     * @return list<PublicSection>
     */
    protected function relatedPublicSections(): array
    {
        return [PublicSection::fromPageSection(static::getResource()::section())];
    }

    protected function getHeaderActions(): array
    {
        /** @var PageSection $section */
        $section = static::getResource()::section();

        return array_values(array_filter([
            $section->hasHeading() ? $this->buildSectionHeadingAction() : null,
            Actions\CreateAction::make(),
        ]));
    }

    protected function buildSectionHeadingAction(): Actions\Action
    {
        /** @var PageSection $section */
        $section = static::getResource()::section();

        return Actions\Action::make('editSectionHeading')
            ->label('Ubah Judul Section')
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->modalHeading("Judul Section {$section->fullLabel()}")
            ->fillForm(function () use ($section): array {
                $heading = SectionHeading::query()->where('section', $section->value)->first();
                $fallback = DefaultPageContent::forCurrentSite(DefaultPageContent::headings()[$section->value]);
                $source = fn (string $key): mixed => $heading ? $heading->{$key} : ($fallback[$key] ?? null);

                return [
                    'title' => $heading?->title ?? $fallback['title'],
                    'subtitle' => $source('subtitle'),
                    'eyebrow' => $source('eyebrow'),
                    'featured_image_path' => $source('featured_image_path'),
                    'featured_icon' => $source('featured_icon'),
                    'featured_title' => $source('featured_title'),
                    'featured_description' => $source('featured_description'),
                ];
            })
            ->form([
                TextInput::make('eyebrow')
                    ->label('Eyebrow')
                    ->helperText('Teks kecil di atas judul.')
                    ->maxLength(60)
                    ->visible($section->hasEyebrow()),
                Textarea::make('title')
                    ->label('Judul')
                    ->helperText('Tekan Enter untuk pindah baris seperti desain.')
                    ->required()
                    ->maxLength($section->headingTitleMaxLength())
                    ->rows(2),
                Textarea::make('subtitle')
                    ->label('Subjudul')
                    ->maxLength($section->headingSubtitleMaxLength())
                    ->rows(2)
                    ->visible($section->hasSubtitle()),
                FileUpload::make('featured_image_path')
                    ->label('Gambar Kartu Besar')
                    ->helperText('Opsional. Bila kosong, gambar bawaan dipakai.')
                    ->image()
                    ->disk('public')
                    ->directory('about-page')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'about-page', maxWidth: 1200))
                    ->visible($section->hasFeaturedCard()),
                Select::make('featured_icon')
                    ->label('Ikon Kartu Besar')
                    ->options(MaterialSymbolsIcons::selectOptions())
                    ->allowHtml()
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->visible($section->hasFeaturedCard()),
                TextInput::make('featured_title')
                    ->label('Judul Kartu Besar')
                    ->required()
                    ->maxLength(160)
                    ->visible($section->hasFeaturedCard()),
                Textarea::make('featured_description')
                    ->label('Deskripsi Kartu Besar')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->visible($section->hasFeaturedCard()),
            ])
            ->action(function (array $data) use ($section): void {
                SectionHeading::query()->updateOrCreate(
                    ['section' => $section->value],
                    [
                        'title' => $data['title'],
                        'subtitle' => $section->hasSubtitle() ? ($data['subtitle'] ?? null) : null,
                        'eyebrow' => $section->hasEyebrow() ? ($data['eyebrow'] ?? null) : null,
                        'featured_image_path' => $section->hasFeaturedCard() ? ($data['featured_image_path'] ?? null) : null,
                        'featured_icon' => $section->hasFeaturedCard() ? ($data['featured_icon'] ?? null) : null,
                        'featured_title' => $section->hasFeaturedCard() ? ($data['featured_title'] ?? null) : null,
                        'featured_description' => $section->hasFeaturedCard() ? ($data['featured_description'] ?? null) : null,
                    ],
                );

                Notification::make()->success()->title('Judul section disimpan.')->send();
            });
    }
}
