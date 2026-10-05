<?php

namespace App\Filament\Resources;

use App\Enums\FaqPlacement;
use App\Enums\PublicSection;
use App\Filament\Resources\FaqItemResource\Pages;
use App\Filament\Support\SectionVisibilityColumn;
use App\Models\FaqItem;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqItemResource extends Resource
{
    protected static ?string $model = FaqItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQ';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('placement')
                    ->label('Tempat Tampil')
                    ->options(FaqPlacement::options())
                    ->default(FaqPlacement::Faq->value)
                    ->required()
                    ->live()
                    ->helperText('Halaman FAQ, atau bagian tanya-jawab di halaman Produk atau Kontak.'),
                TextInput::make('question')
                    ->label('Pertanyaan')
                    ->required()
                    ->maxLength(255),
                Textarea::make('answer')
                    ->label('Jawaban')
                    ->required()
                    ->rows(5),
                TextInput::make('category')
                    ->label('Kategori')
                    ->helperText('Opsional. Dipakai untuk tab kategori di Halaman FAQ.')
                    ->maxLength(100)
                    ->visible(fn ($get): bool => $get('placement') === FaqPlacement::Faq->value || blank($get('placement'))),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('FAQ nonaktif tidak tampil di situs, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(70),
                TextColumn::make('placement')
                    ->label('Tempat')
                    ->badge()
                    ->formatStateUsing(fn (FaqPlacement $state): string => $state->label()),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->placeholder('—'),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                SectionVisibilityColumn::make(fn (FaqItem $record): ?PublicSection => PublicSection::forFaqPlacement($record->placement)),
            ])
            ->filters([
                SelectFilter::make('placement')
                    ->label('Tempat Tampil')
                    ->options(FaqPlacement::options())
                    ->default(FaqPlacement::Faq->value)
                    ->selectablePlaceholder(false),
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
            'index' => Pages\ListFaqItems::route('/'),
            'create' => Pages\CreateFaqItem::route('/create'),
            'edit' => Pages\EditFaqItem::route('/{record}/edit'),
        ];
    }
}
