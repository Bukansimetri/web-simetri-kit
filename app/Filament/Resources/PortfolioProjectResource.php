<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioProjectResource\Pages;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Support\ImageUploads;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PortfolioProjectResource extends Resource
{
    protected static ?string $model = PortfolioProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Portfolio';

    protected static ?string $modelLabel = 'Proyek Portfolio';

    protected static ?string $navigationGroup = 'Portfolio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Proyek')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, ?string $old, Get $get) {
                                if (blank($get('slug')) || $get('slug') === Str::slug($old ?? '')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Otomatis dari judul — bisa diubah manual.'),
                        Select::make('portfolio_category_id')
                            ->label('Kategori')
                            ->options(fn () => PortfolioCategory::orderBy('order')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Angka lebih kecil tampil lebih dulu di /portfolio.'),
                    ])
                    ->columns(2),
                Section::make('Galeri Gambar')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Gambar Proyek')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->minFiles(1)
                            ->required()
                            ->disk('public')
                            ->directory('portfolio')
                            ->helperText('Rekomendasi 1200×900px (rasio bebas). Gambar besar otomatis dikecilkan ke lebar 1200px & dikonversi ke WebP. Gambar pertama = sampul.')
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'portfolio', maxWidth: 1200)),
                    ]),
                Section::make('Deskripsi')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi Proyek')
                            ->required(),
                    ]),
                Section::make('Detail Proyek (opsional)')
                    ->schema([
                        TextInput::make('client_name')
                            ->label('Nama Klien')
                            ->maxLength(255),
                        DatePicker::make('completed_at')
                            ->label('Tanggal Selesai'),
                        TextInput::make('project_url')
                            ->label('URL Tautan Proyek')
                            ->url()
                            ->maxLength(255)
                            ->rule('starts_with:http://,https://')
                            ->helperText('Opsional. Harus diawali http:// atau https://'),
                    ])
                    ->columns(2),
                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Proyek nonaktif tidak tampil di /portfolio, tapi tetap tersimpan.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                ImageColumn::make('images')
                    ->label('Sampul')
                    ->disk('public')
                    ->limit(1)
                    ->limitedRemainingText(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('portfolioCategory.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
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
            'index' => Pages\ListPortfolioProjects::route('/'),
            'create' => Pages\CreatePortfolioProject::route('/create'),
            'edit' => Pages\EditPortfolioProject::route('/{record}/edit'),
        ];
    }
}
