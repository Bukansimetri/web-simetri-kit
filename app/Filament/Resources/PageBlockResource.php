<?php

namespace App\Filament\Resources;

use App\Enums\PageBlockType;
use App\Filament\Resources\PageBlockResource\Pages;
use App\Models\PageBlock;
use App\Support\ImageUploads;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Isi blok halaman tetap (Siapa Kami/Visi Tentang Kami, Info Kontak). Tampilan mengikuti desain halaman.
 * Banner halaman dikelola terpisah di PageBannerResource (menu Banner).
 */
class PageBlockResource extends Resource
{
    protected static ?string $model = PageBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Blok Halaman';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Blok Halaman';

    protected static ?string $pluralModelLabel = 'Blok Halaman';

    protected static ?string $slug = 'blok-halaman';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotIn('block', PageBlockType::pageBannerValues());
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
                Placeholder::make('block_label')
                    ->label('Blok')
                    ->content(fn (): string => $record?->block->label() ?? '—'),
                ...($record ? static::fieldsFor($record->block) : []),
            ])
            ->columns(1);
    }

    /**
     * @return array<int, Component>
     */
    private static function fieldsFor(PageBlockType $type): array
    {
        return match ($type) {
            PageBlockType::AboutWhoWeAre => [
                static::imageField('data.image_path', 'Gambar', $type->imageMaxWidth() ?? 1000),
                TextInput::make('data.badge_text')->label('Teks Badge Overlay')->maxLength(120),
                TextInput::make('data.eyebrow')->label('Label Kecil')->maxLength(60),
                TextInput::make('data.heading')->label('Judul')->maxLength(255),
                RichEditor::make('data.body')->label('Paragraf')->toolbarButtons(['bold', 'italic'])->maxLength(1000),
                RichEditor::make('data.quote')->label('Kutipan')->toolbarButtons(['bold', 'italic'])->maxLength(500),
            ],
            PageBlockType::AboutVision => [
                TextInput::make('data.eyebrow')->label('Label Kecil')->maxLength(60),
                Textarea::make('data.heading')->label('Pernyataan Visi')->rows(3)->maxLength(500),
                Textarea::make('data.subtext')->label('Subteks')->rows(2)->maxLength(500),
            ],
            PageBlockType::ContactInfo => [
                TextInput::make('data.whatsapp_label')->label('Label Tombol WhatsApp')->required()->maxLength(40),
                TextInput::make('data.operating_hours')->label('Jam Operasional')->maxLength(80),
                Textarea::make('data.whatsapp_message')
                    ->label('Pesan Otomatis WhatsApp')
                    ->helperText('Dipakai tombol WhatsApp di halaman Kontak, CTA Beranda, dan CTA di halaman lain. Nomor WhatsApp diatur di Pengaturan Umum.')
                    ->required()
                    ->rows(2)
                    ->maxLength(300),
            ],
            default => [],
        };
    }

    private static function imageField(string $name, string $label, int $maxWidth): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->helperText('Opsional. Gambar otomatis dikonversi ke WebP. Bila kosong, gambar bawaan dipakai.')
            ->image()
            ->disk('public')
            ->directory('about-page')
            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'about-page', maxWidth: $maxWidth));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->paginated(false)
            ->columns([
                TextColumn::make('block')
                    ->label('Blok')
                    ->formatStateUsing(fn (PageBlockType $state): string => $state->label()),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageBlocks::route('/'),
            'edit' => Pages\EditPageBlock::route('/{record}/edit'),
        ];
    }
}
