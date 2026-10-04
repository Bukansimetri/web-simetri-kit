<?php

namespace App\Filament\Resources;

use App\Enums\PageBlockType;
use App\Filament\Clusters\BannerCluster;
use App\Filament\Resources\PageBannerResource\Pages;
use App\Models\PageBlock;
use App\Support\ImageUploads;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Banner (hero) halaman selain Beranda: satu baris per halaman, disimpan sebagai blok halaman.
 * Tampil sebagai tab di menu Banner; desain banner tiap halaman tidak berubah, hanya isinya yang diatur.
 */
class PageBannerResource extends Resource
{
    protected static ?string $model = PageBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banner Halaman';

    protected static ?string $cluster = BannerCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Banner Halaman';

    protected static ?string $pluralModelLabel = 'Banner Halaman';

    protected static ?string $slug = 'banner-halaman';

    public static function getEloquentQuery(): Builder
    {
        $values = PageBlockType::pageBannerValues();
        $cases = implode(' ', array_fill(0, count($values), 'WHEN ? THEN ?'));
        $bindings = collect($values)->flatMap(fn (string $value, int $index): array => [$value, $index])->all();

        return parent::getEloquentQuery()
            ->whereIn('block', $values)
            ->orderByRaw("CASE block {$cases} END", $bindings);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        /** @var PageBlock|null $record */
        $record = $form->getRecord();

        return $form
            ->schema([
                Placeholder::make('page_label')
                    ->label('Halaman')
                    ->content(fn (): string => $record?->block->bannerLabel() ?? '—'),
                ...($record ? static::fieldsFor($record->block) : []),
            ])
            ->columns(1);
    }

    /**
     * Halaman yang desain bannernya bergambar mendapat kolom gambar; sisanya hanya teks.
     *
     * @return array<int, Component>
     */
    private static function fieldsFor(PageBlockType $type): array
    {
        return array_values(array_filter([
            $type->defaultImagePath() !== null ? static::imageField($type) : null,
            TextInput::make('data.title')
                ->label('Judul')
                ->required()
                ->maxLength(160),
            Textarea::make('data.subtitle')
                ->label('Subjudul')
                ->helperText('Opsional. Kosongkan untuk menyembunyikan subjudul.')
                ->rows(2)
                ->maxLength(500),
        ]));
    }

    private static function imageField(PageBlockType $type): FileUpload
    {
        return FileUpload::make('data.image_path')
            ->label('Gambar Latar')
            ->helperText('Opsional. Maks 10MB. Gambar otomatis dikonversi ke WebP. Bila kosong, gambar bawaan dipakai.')
            ->image()
            ->disk('public')
            ->directory('page-banners')
            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
            ->maxSize(10240)
            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'page-banners', maxWidth: $type->imageMaxWidth() ?? 1920));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('block')
                    ->label('Halaman')
                    ->formatStateUsing(fn (PageBlockType $state): string => $state->bannerLabel()),
                TextColumn::make('data.title')
                    ->label('Judul')
                    ->placeholder('—')
                    ->limit(60),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageBanners::route('/'),
            'edit' => Pages\EditPageBanner::route('/{record}/edit'),
        ];
    }
}
