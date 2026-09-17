<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * @return array<string, string>
     */
    private static function statusColors(): array
    {
        return [
            ContactSubmission::STATUS_NEW => 'danger',
            ContactSubmission::STATUS_CONTACTED => 'warning',
            ContactSubmission::STATUS_CLOSED => 'success',
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
                        Placeholder::make('email')
                            ->label('Email')
                            ->content(fn (ContactSubmission $record) => $record->email ?: '-'),
                        Placeholder::make('area')
                            ->label('Area')
                            ->content(fn (ContactSubmission $record) => $record->area ?: '-'),
                        Placeholder::make('topic')
                            ->label('Topik')
                            ->content(fn (ContactSubmission $record) => $record->topic ?: '-'),
                        Placeholder::make('message')
                            ->label('Pesan')
                            ->content(fn (ContactSubmission $record) => $record->message)
                            ->columnSpanFull(),
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
                    ->label('No. HP/WhatsApp')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('area')
                    ->label('Area')
                    ->toggleable(),
                TextColumn::make('topic')
                    ->label('Topik'),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state) => self::statusColors()[$state] ?? 'gray')
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
                Filter::make('belum_dihubungi')
                    ->label('Belum Dihubungi')
                    ->query(fn (Builder $query) => $query->where('status', ContactSubmission::STATUS_NEW))
                    ->toggle(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('dari')->native(false),
                        DatePicker::make('sampai')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['sampai'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Action::make('tandai_dihubungi')
                    ->label('Tandai Sudah Dihubungi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContactSubmission $record) => $record->status === ContactSubmission::STATUS_NEW)
                    ->requiresConfirmation()
                    ->action(fn (ContactSubmission $record) => $record->update(['status' => ContactSubmission::STATUS_CONTACTED])),
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

    public static function getNavigationBadge(): ?string
    {
        return strval(static::getModel()::where('status', ContactSubmission::STATUS_NEW)->count()) ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
