<?php

namespace App\Filament\Resources;

use App\Enums\CtaPlacement;
use App\Filament\Resources\CallToActionResource\Pages;
use App\Models\CallToAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Teks CTA pada penempatan tetap. Tujuan, ikon, dan tampilan tombol tetap di Blade.
 */
class CallToActionResource extends Resource
{
    protected static ?string $model = CallToAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'CTA';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'CTA';

    protected static ?string $pluralModelLabel = 'CTA';

    protected static ?string $slug = 'cta';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $placementOf = fn (?CallToAction $record): ?CtaPlacement => $record?->placement;

        return $form
            ->schema([
                Placeholder::make('placement_label')
                    ->label('Penempatan')
                    ->content(fn (?CallToAction $record): string => $record?->placement->label() ?? '—'),
                Textarea::make('title')
                    ->label('Judul')
                    ->helperText('Tekan Enter untuk pindah baris seperti desain.')
                    ->required()
                    ->maxLength(80)
                    ->rows(2),
                Textarea::make('body')
                    ->label(fn (?CallToAction $record): string => $placementOf($record)?->bodyLabel() ?? 'Subjudul')
                    ->helperText(fn (?CallToAction $record): ?string => $placementOf($record)?->supportsProductToken()
                        ? 'Tulis {produk} untuk menyisipkan nama produk yang sedang dibuka.'
                        : null)
                    ->maxLength(300)
                    ->rows(3),
                TextInput::make('primary_label')
                    ->label(fn (?CallToAction $record): string => $placementOf($record) === CtaPlacement::Home ? 'Label Tombol WhatsApp' : 'Label Tombol')
                    ->required()
                    ->maxLength(40),
                TextInput::make('secondary_label')
                    ->label('Label Tombol Form')
                    ->required(fn (?CallToAction $record): bool => (bool) $placementOf($record)?->hasSecondaryButton())
                    ->visible(fn (?CallToAction $record): bool => (bool) $placementOf($record)?->hasSecondaryButton())
                    ->maxLength(40),
                Placeholder::make('hint')
                    ->hiddenLabel()
                    ->content('Tujuan tombol mengikuti pengaturan situs dan tidak bisa diubah di sini.'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->paginated(false)
            ->columns([
                TextColumn::make('placement')
                    ->label('Penempatan')
                    ->formatStateUsing(fn (CtaPlacement $state): string => $state->label()),
                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(60),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallToActions::route('/'),
            'edit' => Pages\EditCallToAction::route('/{record}/edit'),
        ];
    }
}
