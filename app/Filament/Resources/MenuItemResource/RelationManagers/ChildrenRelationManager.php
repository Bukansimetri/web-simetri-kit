<?php

namespace App\Filament\Resources\MenuItemResource\RelationManagers;

use App\Filament\Resources\MenuItemResource;
use App\Models\MenuItem;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Sub-menu (satu tingkat) untuk sebuah item induk — reorder di sini otomatis
 * terbatas pada anak-anak item induk yang sama (research.md #7, FR-005).
 */
class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Sub-menu';

    public function form(Form $form): Form
    {
        return $form->schema(MenuItemResource::linkFieldsSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->defaultSort('order_column')
            ->reorderable('order_column')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Label'),
                Tables\Columns\TextColumn::make('link_summary')
                    ->label('Tautan')
                    ->state(fn (MenuItem $record): string => MenuItemResource::linkSummary($record)),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['menu_location_id'] = $this->getOwnerRecord()->menu_location_id;

                        return $data;
                    }),
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
}
