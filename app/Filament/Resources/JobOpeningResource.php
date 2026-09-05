<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOpeningResource\Pages;
use App\Models\JobOpening;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Validation\Rule;

class JobOpeningResource extends Resource
{
    protected static ?string $model = JobOpening::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Lowongan Kerja';

    protected static ?string $modelLabel = 'Lowongan Kerja';

    protected static ?string $navigationGroup = 'Karir';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul Posisi')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Lokasi')
                    ->required()
                    ->maxLength(255),
                Select::make('employment_type')
                    ->label('Tipe Pekerjaan')
                    ->options(JobOpening::EMPLOYMENT_TYPES)
                    ->required()
                    ->rule(Rule::in(array_keys(JobOpening::EMPLOYMENT_TYPES))),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->rows(4),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Lowongan nonaktif tidak tampil di halaman publik /karir, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Posisi')
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->label('Tipe Pekerjaan')
                    ->formatStateUsing(fn (string $state) => JobOpening::EMPLOYMENT_TYPES[$state] ?? $state),
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
            'index' => Pages\ListJobOpenings::route('/'),
            'create' => Pages\CreateJobOpening::route('/create'),
            'edit' => Pages\EditJobOpening::route('/{record}/edit'),
        ];
    }
}
