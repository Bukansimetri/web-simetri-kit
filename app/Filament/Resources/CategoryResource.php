<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Kategori Produk';

    protected static ?string $modelLabel = 'Kategori Produk';

    protected static ?string $navigationGroup = 'Katalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable(),
                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label('Jumlah Produk')
                    ->counts('products'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Category $record, DeleteAction $action) {
                        if ($record->products()->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Kategori masih dipakai')
                                ->body('Kategori ini masih dipakai oleh satu atau lebih produk. Pindahkan atau hapus produk tsb terlebih dahulu.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action) {
                            $inUse = $records->filter(fn (Category $category) => $category->products()->count() > 0);

                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian kategori masih dipakai')
                                    ->body('Kategori berikut masih dipakai produk dan tidak dihapus: '.$inUse->pluck('name')->implode(', '))
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('products');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
