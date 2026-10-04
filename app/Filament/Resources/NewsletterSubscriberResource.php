<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Langganan Newsletter';

    protected static ?string $modelLabel = 'Pelanggan Newsletter';

    protected static ?string $navigationGroup = 'Prospek & Pesan';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'email';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('subscribed_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('subscribed_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
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
            'index' => Pages\ListNewsletterSubscribers::route('/'),
        ];
    }

    /**
     * Pendaftar hanya masuk lewat formulir publik, tidak dibuat manual dari panel.
     */
    public static function canCreate(): bool
    {
        return false;
    }
}
