<?php

namespace App\Filament\Support;

use App\Enums\PageSection;
use App\Models\SectionItem;
use App\Rules\WithinActiveItemLimit;
use App\Support\MaterialSymbolsIcons;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

/**
 * Dasar resource untuk satu section daftar item. Diletakkan di luar folder discovery agar tidak terdaftar sendiri.
 */
abstract class SectionItemResource extends Resource
{
    protected static ?string $model = SectionItem::class;

    abstract public static function section(): PageSection;

    public static function getNavigationLabel(): string
    {
        return static::section()->label();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::section()->navigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return static::section()->navigationSort();
    }

    public static function getPluralModelLabel(): string
    {
        return static::section()->fullLabel();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('section', static::section()->value);
    }

    public static function form(Form $form): Form
    {
        $section = static::section();

        $limitHint = "Maksimal {$section->maxActiveItems()} item aktif tampil di halaman {$section->pageName()}.";

        return $form
            ->schema(array_values(array_filter([
                Select::make('icon')
                    ->label('Ikon')
                    ->options(MaterialSymbolsIcons::selectOptions())
                    ->allowHtml()
                    ->searchable()
                    ->required()
                    ->rule(Rule::in(MaterialSymbolsIcons::keys()))
                    ->validationMessages(['in' => 'Pilih ikon dari daftar yang tersedia.'])
                    ->visible($section->hasIcon()),
                TextInput::make('title')
                    ->label($section->itemTitleLabel())
                    ->required()
                    ->maxLength($section->itemTitleMaxLength()),
                Textarea::make('description')
                    ->label($section->itemDescriptionLabel())
                    ->required()
                    ->maxLength($section->itemDescriptionMaxLength())
                    ->rows(3),
                $section->supportsEmphasis()
                    ? Toggle::make('is_emphasized')
                        ->label('Tonjolkan')
                        ->helperText('Hanya satu item yang bisa ditonjolkan; item lain otomatis dilepas.')
                    : null,
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText($limitHint)
                    ->default(true)
                    ->rules(fn (?SectionItem $record): array => [new WithinActiveItemLimit($section, $record?->getKey())]),
            ])))
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        $section = static::section();

        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->paginated(false)
            ->description("Maksimal {$section->maxActiveItems()} item aktif tampil di halaman {$section->pageName()}. Urutan di sini = urutan tampil.")
            ->columns([
                TextColumn::make('icon')
                    ->label('Ikon')
                    ->html()
                    ->formatStateUsing(fn (?string $state): string => '<span class="material-symbols-outlined">'.e($state).'</span>')
                    ->visible($section->hasIcon()),
                TextColumn::make('title')
                    ->label($section->itemTitleLabel()),
                IconColumn::make('is_emphasized')
                    ->label('Ditonjolkan')
                    ->boolean()
                    ->visible($section->supportsEmphasis()),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->rules(fn (SectionItem $record): array => [new WithinActiveItemLimit($section, $record->getKey())]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
