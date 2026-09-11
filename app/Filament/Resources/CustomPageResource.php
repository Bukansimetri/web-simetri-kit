<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomPageResource\Pages;
use App\Models\CustomPage;
use App\Support\ImageUploads;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CustomPageResource extends Resource
{
    protected static ?string $model = CustomPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Halaman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, ?string $old, Get $get) {
                        // Auto-generate slug dari judul (FR-003) — hanya kalau slug
                        // belum diisi manual berbeda dari hasil slug judul sebelumnya.
                        if (blank($get('slug')) || $get('slug') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Otomatis dari judul — bisa diubah manual. Diakses lewat /halaman/{slug}.'),
                RichEditor::make('content')
                    ->label('Isi Halaman')
                    ->required()
                    ->columnSpanFull(),
                Section::make('SEO')
                    ->collapsed()
                    ->description('Kosongkan untuk pakai default otomatis dari data halaman.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Judul Pencarian')
                            ->maxLength(255)
                            ->live()
                            ->hint(fn (Get $get) => strlen($get('meta_title') ?? '').'/60 karakter disarankan'),
                        Textarea::make('meta_description')
                            ->label('Deskripsi Pencarian')
                            ->maxLength(500)
                            ->rows(3)
                            ->live()
                            ->hint(fn (Get $get) => strlen($get('meta_description') ?? '').'/160 karakter disarankan'),
                        FileUpload::make('meta_image_path')
                            ->label('Gambar SEO / Share Sosial')
                            ->image()
                            ->disk('public')
                            ->directory('seo')
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'seo', maxWidth: 1200))
                            ->helperText('Opsional. Rekomendasi 1200×630px. Kosongkan untuk pakai gambar OG default situs.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomPages::route('/'),
            'create' => Pages\CreateCustomPage::route('/create'),
            'edit' => Pages\EditCustomPage::route('/{record}/edit'),
        ];
    }
}
