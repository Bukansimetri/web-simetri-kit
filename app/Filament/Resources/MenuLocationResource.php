<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuLocationResource\Pages;
use App\Models\MenuLocation;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MenuLocationResource extends Resource
{
    protected static ?string $model = MenuLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Lokasi Menu';

    protected static ?string $navigationGroup = 'Menu Builder';

    protected static ?string $modelLabel = 'Lokasi Menu';

    protected static ?string $pluralModelLabel = 'Lokasi Menu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lokasi')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Dipakai kode untuk memanggil lokasi ini, mis. <x-layout.menu location="slug-ini" />')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->helperText('Opsional. Bantuan konteks untuk admin lain, mis. "Tampil di header seluruh halaman publik".')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge(),
                Tables\Columns\TextColumn::make('menuItems_count')
                    ->label('Jumlah Item')
                    ->counts('menuItems'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuLocations::route('/'),
            'create' => Pages\CreateMenuLocation::route('/create'),
            'edit' => Pages\EditMenuLocation::route('/{record}/edit'),
        ];
    }
}
