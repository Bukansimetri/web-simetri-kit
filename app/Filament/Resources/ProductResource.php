<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?string $navigationGroup = 'Katalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, ?string $old, Get $get) {
                                // Auto-generate slug dari nama (FR-006) — hanya kalau slug
                                // belum diisi manual berbeda dari hasil slug nama sebelumnya.
                                if (blank($get('slug')) || $get('slug') === Str::slug($old ?? '')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Otomatis dari nama produk — bisa diubah manual.'),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->options(fn () => Category::orderBy('order')->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Deskripsi')
                    ->schema([
                        Textarea::make('short_description')
                            ->label('Deskripsi Singkat')
                            ->helperText('Ditampilkan di kartu produk.')
                            ->required()
                            ->rows(2),
                        Textarea::make('description')
                            ->label('Deskripsi Lengkap')
                            ->helperText('Ditampilkan di halaman detail produk.')
                            ->required()
                            ->rows(5),
                    ]),
                Section::make('Harga')
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('strikethrough_price')
                            ->label('Harga Coret (opsional)')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR'),
                TextColumn::make('order')
                    ->label('Urutan')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
