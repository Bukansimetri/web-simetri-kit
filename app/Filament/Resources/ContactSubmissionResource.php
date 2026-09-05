<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Pesan Masuk';

    protected static ?string $modelLabel = 'Pesan Kontak';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return [
            ContactSubmission::STATUS_NEW => 'Baru',
            ContactSubmission::STATUS_CONTACTED => 'Sudah Dihubungi',
            ContactSubmission::STATUS_CLOSED => 'Selesai',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Pesan')
                    ->schema([
                        Placeholder::make('name')
                            ->label('Nama')
                            ->content(fn (ContactSubmission $record) => $record->name),
                        Placeholder::make('phone')
                            ->label('No. HP/WhatsApp')
                            ->content(fn (ContactSubmission $record) => $record->phone),
                        Placeholder::make('topic')
                            ->label('Topik')
                            ->content(fn (ContactSubmission $record) => $record->topic ?: '-'),
                        Placeholder::make('message')
                            ->label('Pesan')
                            ->content(fn (ContactSubmission $record) => $record->message),
                        Placeholder::make('created_at')
                            ->label('Waktu Masuk')
                            ->content(fn (ContactSubmission $record) => $record->created_at->translatedFormat('d F Y H:i')),
                        Select::make('status')
                            ->label('Status')
                            ->options(self::statusOptions())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('No. HP/WhatsApp'),
                TextColumn::make('topic')
                    ->label('Topik'),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => self::statusOptions()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'edit' => Pages\EditContactSubmission::route('/{record}/edit'),
        ];
    }
}
