<?php

namespace App\Filament\Resources;

use App\Enums\CustomPageTemplate;
use App\Filament\Resources\CustomPageResource\Pages;
use App\Models\CustomPage;
use App\Support\ImageUploads;
use App\Support\MaterialSymbolsIcons;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
use Illuminate\Validation\Rule;

class CustomPageResource extends Resource
{
    protected static ?string $model = CustomPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Halaman';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 1;

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
                Select::make('template')
                    ->label('Template')
                    ->options(CustomPageTemplate::options())
                    ->default(CustomPageTemplate::Standard->value)
                    ->required()
                    ->native(false)
                    ->live()
                    ->helperText('Standar: satu kolom teks bebas. Dokumen Legal: halaman berstruktur dengan daftar isi, pasal, dan kartu (untuk Kebijakan Privasi, Syarat & Ketentuan, dan sejenisnya).'),
                RichEditor::make('content')
                    ->label('Isi Halaman')
                    ->required()
                    ->visible(fn (Get $get): bool => self::isStandard($get('template')))
                    ->columnSpanFull(),
                self::legalSection(),
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
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'seo', maxWidth: 1200))
                            ->helperText('Opsional. Rekomendasi 1200×630px. Kosongkan untuk pakai gambar OG default situs.'),
                    ]),
            ]);
    }

    private static function isStandard(mixed $template): bool
    {
        return blank($template) || $template === CustomPageTemplate::Standard->value || $template === CustomPageTemplate::Standard;
    }

    private static function legalSection(): Section
    {
        return Section::make('Dokumen Legal')
            ->statePath('legal')
            ->columnSpanFull()
            ->visible(fn (Get $get): bool => ! self::isStandard($get('template')))
            ->schema([
                FileUpload::make('hero_image_path')
                    ->label('Gambar Latar Judul')
                    ->image()
                    ->disk('public')
                    ->directory('legal-pages')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->maxSize(10240)
                    ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'legal-pages', maxWidth: 1920))
                    ->helperText('Opsional. Kosongkan untuk memakai gambar bawaan.'),
                Textarea::make('subtitle')
                    ->label('Subjudul')
                    ->rows(2)
                    ->maxLength(500),
                RichEditor::make('intro')
                    ->label('Paragraf Pembuka')
                    ->columnSpanFull(),
                Fieldset::make('Kotak Sorotan')
                    ->schema([
                        TextInput::make('highlight_title')->label('Judul Sorotan')->maxLength(160),
                        Textarea::make('highlight_body')->label('Isi Sorotan')->rows(3)->maxLength(600),
                    ])->columns(1),
                Repeater::make('sections')
                    ->label('Bagian / Pasal')
                    ->helperText('Nomor bagian dan daftar isi dibuat otomatis dari urutan. Seret untuk mengubah urutan.')
                    ->reorderable()
                    ->collapsible()
                    ->cloneable()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Bagian')
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('label')->label('Label Kecil')->maxLength(60)->helperText('Opsional, mis. PENDAHULUAN. Tampil sebagai "PASAL 01 · PENDAHULUAN".'),
                        TextInput::make('title')->label('Judul Bagian')->required()->maxLength(160),
                        RichEditor::make('body')->label('Isi Bagian')->columnSpanFull(),
                        Repeater::make('cards')
                            ->label('Kartu')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Kartu')
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('icon')
                                    ->label('Ikon')
                                    ->options(MaterialSymbolsIcons::selectOptions())
                                    ->allowHtml()
                                    ->searchable()
                                    ->native(false)
                                    ->rules([Rule::in(MaterialSymbolsIcons::keys())]),
                                TextInput::make('title')->label('Judul Kartu')->required()->maxLength(100),
                                Textarea::make('text')->label('Teks Kartu')->rows(2)->maxLength(400)->columnSpanFull(),
                            ])->columns(2),
                        Textarea::make('note')->label('Catatan Kecil')->rows(2)->maxLength(400)->columnSpanFull(),
                    ])->columns(2),
                Fieldset::make('Kotak Kontak (Sidebar)')
                    ->schema([
                        TextInput::make('contact_title')->label('Judul')->maxLength(120),
                        Textarea::make('contact_text')->label('Teks')->rows(2)->maxLength(400),
                        TextInput::make('contact_whatsapp_label')->label('Label Tombol WhatsApp')->maxLength(60)
                            ->helperText('Tombol memakai nomor WhatsApp di Pengaturan Umum; tidak tampil bila nomor kosong.'),
                        Textarea::make('contact_whatsapp_message')->label('Pesan WhatsApp')->rows(2)->maxLength(300),
                        TextInput::make('contact_email')->label('Email')->email()->maxLength(255),
                    ])->columns(1),
                Fieldset::make('Berkas PDF')
                    ->schema([
                        FileUpload::make('pdf_path')
                            ->label('Berkas PDF')
                            ->disk('public')
                            ->directory('legal-pages/pdf')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->preserveFilenames()
                            ->helperText('Opsional. Tombol unduh hanya tampil bila PDF diunggah. Maksimal 10 MB.'),
                        TextInput::make('pdf_label')->label('Label Tombol Unduh')->maxLength(80)->placeholder('Unduh Dokumen (PDF)'),
                    ])->columns(1),
                Fieldset::make('CTA Penutup')
                    ->schema([
                        TextInput::make('cta_title')->label('Judul')->maxLength(160)->helperText('Kosongkan untuk menyembunyikan CTA penutup.'),
                        Textarea::make('cta_body')->label('Teks')->rows(2)->maxLength(300),
                        TextInput::make('cta_button_label')->label('Label Tombol')->maxLength(60),
                        TextInput::make('cta_button_url')->label('Tautan Tombol')->maxLength(255)->placeholder('/kontak'),
                    ])->columns(1),
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
                TextColumn::make('template')
                    ->label('Template')
                    ->badge()
                    ->formatStateUsing(fn (CustomPageTemplate $state): string => $state->label()),
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
