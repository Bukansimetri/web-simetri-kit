<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Filament\Resources\MenuItemResource\RelationManagers;
use App\Models\CustomPage;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Item Menu';

    protected static ?string $navigationGroup = 'Menu Builder';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Item Menu';

    protected static ?string $pluralModelLabel = 'Item Menu';

    /**
     * Jenis konten internal yang boleh dijadikan tujuan tautan (research.md #4).
     * Didaftarkan eksplisit (bukan auto-discovery) agar admin per klien hanya
     * melihat jenis konten yang relevan untuk instalasi tersebut.
     *
     * @var array<string, array{label: string, model: class-string}>
     */
    private const LINKABLE_TYPES = [
        'custom-page' => ['label' => 'Halaman (Custom Page)', 'model' => CustomPage::class],
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('menu_location_id')
                    ->label('Lokasi Menu')
                    ->relationship('menuLocation', 'name')
                    ->required(),
                ...self::linkFieldsSchema(),
            ]);
    }

    /**
     * Field label + tujuan tautan + toggle, dipakai oleh form resource utama
     * (item induk) maupun ChildrenRelationManager (sub-menu, tanpa field
     * lokasi karena diwarisi otomatis dari induknya — lihat research.md #7).
     *
     * @return array<int, Component>
     */
    public static function linkFieldsSchema(): array
    {
        return [
            TextInput::make('label')
                ->label('Label')
                ->required()
                ->maxLength(255),
            Radio::make('link_type')
                ->label('Tujuan Tautan')
                ->options([
                    MenuItem::LINK_TYPE_INTERNAL => 'Halaman Internal',
                    MenuItem::LINK_TYPE_EXTERNAL => 'URL Eksternal',
                    MenuItem::LINK_TYPE_NONE => 'Tanpa Tautan (label saja)',
                ])
                ->default(MenuItem::LINK_TYPE_NONE)
                ->live()
                ->required(),
            Select::make('linkable_type')
                ->label('Jenis Halaman')
                ->options(collect(self::LINKABLE_TYPES)->map(fn (array $type) => $type['label'])->all())
                ->live()
                ->visible(fn ($get) => $get('link_type') === MenuItem::LINK_TYPE_INTERNAL)
                ->required(fn ($get) => $get('link_type') === MenuItem::LINK_TYPE_INTERNAL),
            Select::make('linkable_id')
                ->label('Halaman')
                ->options(function ($get) {
                    $type = self::LINKABLE_TYPES[$get('linkable_type')] ?? null;

                    if ($type === null) {
                        return [];
                    }

                    return $type['model']::query()->pluck('title', 'id');
                })
                ->visible(fn ($get) => $get('link_type') === MenuItem::LINK_TYPE_INTERNAL)
                ->required(fn ($get) => $get('link_type') === MenuItem::LINK_TYPE_INTERNAL),
            TextInput::make('external_url')
                ->label('URL Eksternal')
                ->url()
                ->maxLength(255)
                ->visible(fn ($get) => $get('link_type') === MenuItem::LINK_TYPE_EXTERNAL)
                ->required(fn ($get) => $get('link_type') === MenuItem::LINK_TYPE_EXTERNAL),
            Toggle::make('open_in_new_tab')
                ->label('Buka di Tab Baru'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            // Tabel utama hanya menampilkan item induk (root); sub-menu
            // dikelola & di-reorder terpisah lewat relation manager pada
            // item induknya (research.md #7) agar reorder tidak pernah
            // mencampur item dari parent/lokasi berbeda.
            ->modifyQueryUsing(fn (Builder $query) => $query->root())
            ->defaultSort('order_column')
            ->reorderable('order_column')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('menuLocation.name')
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('link_summary')
                    ->label('Tautan')
                    ->state(fn (MenuItem $record): string => self::linkSummary($record))
                    ->badge(fn (MenuItem $record): bool => self::hasBrokenLink($record))
                    ->color(fn (MenuItem $record): string => self::hasBrokenLink($record) ? 'danger' : 'gray'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('menu_location_id')
                    ->label('Lokasi Menu')
                    ->relationship('menuLocation', 'name')
                    ->default(fn () => MenuLocation::query()->orderBy('id')->value('id')),
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

    public static function linkSummary(MenuItem $record): string
    {
        if ($record->link_type === MenuItem::LINK_TYPE_NONE) {
            return '—';
        }

        return $record->resolveUrl() ?? 'Tautan tidak valid';
    }

    /**
     * Badge peringatan (contracts/menu-rendering-contract.md §3): tautan
     * internal yang targetnya sudah tidak ada/terhapus.
     */
    public static function hasBrokenLink(MenuItem $record): bool
    {
        return $record->link_type === MenuItem::LINK_TYPE_INTERNAL && $record->resolveUrl() === null;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
