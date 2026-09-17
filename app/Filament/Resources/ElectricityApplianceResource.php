<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElectricityApplianceResource\Pages;
use App\Models\ElectricityAppliance;
use App\Support\MaterialSymbolsIcons;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ElectricityApplianceResource extends Resource
{
    protected static ?string $model = ElectricityAppliance::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Peralatan Listrik';

    protected static ?string $modelLabel = 'Peralatan Listrik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Peralatan')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Identitas unik dipakai kalkulator. Ubah hanya bila benar-benar perlu — lead lama tetap menyimpan nama & watt saat itu.')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->alphaDash(),
                Select::make('icon')
                    ->label('Ikon')
                    ->options(MaterialSymbolsIcons::selectOptions())
                    ->allowHtml()
                    ->searchable()
                    ->required()
                    ->default('bolt'),
                TextInput::make('watt')
                    ->label('Daya (Watt)')
                    ->helperText('Estimasi konsumsi daya satu unit peralatan ini.')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->helperText('Angka lebih kecil tampil lebih dulu.')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Peralatan nonaktif tidak muncul di kalkulator publik, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('icon')
                    ->label('Ikon')
                    ->formatStateUsing(fn (string $state) => '<span class="material-symbols-outlined" style="font-size:1.25rem;vertical-align:middle;">'.$state.'</span>')
                    ->html(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->badge(),
                TextColumn::make('watt')
                    ->label('Watt')
                    ->numeric()
                    ->sortable()
                    ->suffix(' W'),
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
            'index' => Pages\ManageElectricityAppliances::route('/'),
        ];
    }
}
