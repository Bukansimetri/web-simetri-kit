<?php

namespace App\Filament\Resources;

use App\Enums\BannerOverlayStyle;
use App\Enums\BannerTextPosition;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use App\Support\ImageUploads;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Validation\Rules\Enum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 2;

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

    /**
     * Aturan alamat CTA (FR-004): path internal berawalan `/` maupun URL
     * absolut http/https. Berbeda dari `link_url` lama yang mewajibkan
     * awalan http://https:// — aturan link_url tidak diubah.
     */
    private static function ctaUrlRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if (blank($value)) {
                return;
            }

            if (! preg_match('#^(https?://|/)#', (string) $value)) {
                $fail('Alamat harus diawali dengan http://, https://, atau /.');
            }
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas & Gambar')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Internal')
                            ->helperText('Untuk identifikasi di panel — tidak tampil ke pengunjung.')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('image_path')
                            ->label('Gambar Banner')
                            ->helperText('Wajib. Maks 10MB. Rekomendasi 1600×600px. Gambar besar otomatis dikecilkan ke lebar 1600px & dikonversi ke WebP.')
                            ->image()
                            ->required()
                            ->disk('public')
                            ->directory('banners')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->maxSize(10240)
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'banners', maxWidth: 1600)),
                        TextInput::make('alt_text')
                            ->label('Teks Alt')
                            ->helperText('Teks alternatif gambar untuk aksesibilitas.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('link_url')
                            ->label('URL Tautan (Warisan)')
                            ->helperText('Opsional. Hanya dipakai bila slide tidak memiliki tombol CTA di bawah. Harus diawali http:// atau https://')
                            ->url()
                            ->maxLength(255)
                            ->rule('starts_with:http://,https://'),
                    ]),
                Section::make('Konten Slide')
                    ->description('Teks yang dilihat pengunjung di atas gambar. Kosongkan seluruhnya untuk menampilkan gambar polos.')
                    ->schema([
                        TextInput::make('badge_text')
                            ->label('Teks Badge')
                            ->helperText('Opsional. Pil kecil di atas judul, mis. "Solar Panel Terpercaya".')
                            ->maxLength(120),
                        TextInput::make('heading')
                            ->label('Judul Slide')
                            ->helperText('Opsional. Ini judul yang dilihat pengunjung — berbeda dari Judul Internal di atas.')
                            ->maxLength(160),
                        Textarea::make('subheading')
                            ->label('Subjudul')
                            ->helperText('Opsional, maks 400 karakter.')
                            ->maxLength(400)
                            ->rows(3),
                    ]),
                Section::make('Tombol CTA')
                    ->description('Isi label dan alamat berpasangan. Mengisi salah satu saja akan ditolak saat simpan.')
                    ->schema([
                        TextInput::make('cta_primary_label')
                            ->label('Label Tombol Utama')
                            ->maxLength(60)
                            ->requiredWith('cta_primary_url'),
                        TextInput::make('cta_primary_url')
                            ->label('Alamat Tombol Utama')
                            ->helperText('Path internal (mis. /kontak) atau URL absolut http(s)://')
                            ->maxLength(255)
                            ->requiredWith('cta_primary_label')
                            ->rule(fn () => self::ctaUrlRule()),
                        TextInput::make('cta_secondary_label')
                            ->label('Label Tombol Sekunder')
                            ->maxLength(60)
                            ->requiredWith('cta_secondary_url'),
                        TextInput::make('cta_secondary_url')
                            ->label('Alamat Tombol Sekunder')
                            ->helperText('Path internal (mis. /kontak) atau URL absolut http(s)://')
                            ->maxLength(255)
                            ->requiredWith('cta_secondary_label')
                            ->rule(fn () => self::ctaUrlRule()),
                    ]),
                Section::make('Trust Bar')
                    ->description('Opsional. Area bukti sosial di bawah tombol — teks, avatar, atau lencana sertifikasi.')
                    ->schema([
                        RichEditor::make('trust_html')
                            ->label('Konten Trust Bar')
                            ->helperText('Opsional. Toolbar terbatas: tebal, miring, tautan, daftar, dan gambar.')
                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'attachFiles'])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('banners/trust')
                            ->saveUploadedFileAttachmentsUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'banners/trust'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Tampilan')
                    ->description('Preset lapisan dan posisi teks. Warna tombol tetap mengikuti tema brand (FR-006).')
                    ->schema([
                        Select::make('overlay_style')
                            ->label('Gaya Lapisan')
                            ->options(collect(BannerOverlayStyle::cases())->mapWithKeys(fn (BannerOverlayStyle $case) => [$case->value => $case->label()]))
                            ->default(BannerOverlayStyle::Dark->value)
                            ->required()
                            ->native(false)
                            ->rule(new Enum(BannerOverlayStyle::class)),
                        Select::make('text_position')
                            ->label('Posisi Teks')
                            ->options(collect(BannerTextPosition::cases())->mapWithKeys(fn (BannerTextPosition $case) => [$case->value => $case->label()]))
                            ->default(BannerTextPosition::Left->value)
                            ->required()
                            ->native(false)
                            ->rule(new Enum(BannerTextPosition::class)),
                    ]),
                Section::make('Penjadwalan')
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('Mulai Tayang')
                            ->helperText('Opsional. Kosong = tayang sejak sekarang.'),
                        DatePicker::make('ends_at')
                            ->label('Selesai Tayang')
                            ->helperText('Opsional. Kosong = tayang tanpa batas akhir.')
                            ->afterOrEqual('starts_at'),
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->helperText('Angka lebih kecil tampil lebih dulu / lebih awal di slider.')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Banner nonaktif tidak tampil di beranda, tapi tetap tersimpan di sini.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public'),
                TextColumn::make('title')
                    ->label('Judul Internal')
                    ->searchable(),
                TextColumn::make('heading')
                    ->label('Judul Slide')
                    ->placeholder('—')
                    ->limit(40),
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
