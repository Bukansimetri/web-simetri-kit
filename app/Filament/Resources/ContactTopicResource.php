<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactTopicResource\Pages;
use App\Models\ContactTopic;
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

class ContactTopicResource extends Resource
{
    protected static ?string $model = ContactTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Prospek & Pesan';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Topik Form Kontak';

    protected static ?string $modelLabel = 'Topik Form Kontak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Topik')
                    ->helperText('Teks yang dilihat pengunjung di dropdown "Topik Kebutuhan" pada form Kontak.')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Identitas unik yang disimpan di Pesan Masuk. Ubah hanya bila benar-benar perlu — pesan lama tetap menyimpan slug sebelumnya.')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->alphaDash(),
                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->helperText('Angka lebih kecil tampil lebih dulu.')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Topik nonaktif tidak muncul di form Kontak, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->badge(),
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
            'index' => Pages\ManageContactTopics::route('/'),
        ];
    }
}
