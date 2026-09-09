<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use App\Support\ImageUploads;
use Filament\Forms\Components\DatePicker;
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

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $modelLabel = 'Banner';

    /**
     * @var array<string, string>
     */
    private const STATUS_LABELS = [
        'live' => 'Tayang',
        'scheduled' => 'Terjadwal',
        'expired' => 'Kedaluwarsa',
        'inactive' => 'Nonaktif',
    ];

    /**
     * @var array<string, string>
     */
    private const STATUS_COLORS = [
        'live' => 'success',
        'scheduled' => 'warning',
        'expired' => 'danger',
        'inactive' => 'gray',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul Internal')
                    ->helperText('Untuk identifikasi di panel — tidak tampil ke pengunjung.')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('image_path')
                    ->label('Gambar Banner')
                    ->helperText('Wajib. Rekomendasi 1600×600px. Gambar besar otomatis dikecilkan ke lebar 1600px & dikonversi ke WebP.')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('banners')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'banners', maxWidth: 1600)),
                TextInput::make('alt_text')
                    ->label('Teks Alt')
                    ->helperText('Teks alternatif gambar untuk aksesibilitas.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('link_url')
                    ->label('URL Tautan')
                    ->helperText('Opsional. Harus diawali http:// atau https://')
                    ->url()
                    ->maxLength(255)
                    ->rule('starts_with:http://,https://'),
                DatePicker::make('starts_at')
                    ->label('Mulai Tayang')
                    ->helperText('Opsional. Kosong = tayang sejak sekarang.'),
                DatePicker::make('ends_at')
                    ->label('Selesai Tayang')
                    ->helperText('Opsional. Kosong = tayang tanpa batas akhir.')
                    ->afterOrEqual('starts_at'),
                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->helperText('Angka lebih kecil tampil lebih dulu / lebih awal di carousel.')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Banner nonaktif tidak tampil di beranda, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public'),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status Tayang')
                    ->badge()
                    ->state(fn (Banner $record): string => $record->displayStatus())
                    ->formatStateUsing(fn (string $state): string => self::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->placeholder('—'),
                TextColumn::make('ends_at')
                    ->label('Selesai')
                    ->date('d M Y')
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
