<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientLogoResource\Pages;
use App\Models\ClientLogo;
use App\Support\ImageUploads;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ClientLogoResource extends Resource
{
    protected static ?string $model = ClientLogo::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Logo Klien';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Logo Klien';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('company_name')
                    ->label('Nama Perusahaan')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->helperText('Wajib. PNG/JPG/WebP — otomatis dikonversi ke WebP. Rekomendasi latar transparan.')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('client-logos')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'client-logos')),
                TextInput::make('link_url')
                    ->label('URL Tautan')
                    ->helperText('Opsional. Harus diawali http:// atau https://')
                    ->url()
                    ->maxLength(255)
                    ->rule('starts_with:http://,https://'),
                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->helperText('Angka lebih kecil tampil lebih dulu.')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Logo nonaktif tidak tampil di halaman Tentang Kami, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public'),
                TextColumn::make('company_name')
                    ->label('Nama Perusahaan')
                    ->searchable(),
                TextColumn::make('link_url')
                    ->label('Tautan')
                    ->url(fn (?string $state) => $state)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
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
            'index' => Pages\ListClientLogos::route('/'),
            'create' => Pages\CreateClientLogo::route('/create'),
            'edit' => Pages\EditClientLogo::route('/{record}/edit'),
        ];
    }
}
