<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

/**
 * Kelola akun admin panel dan penetapan peran (Role) ke tiap akun. Hanya
 * super_admin yang boleh mengakses — resource ini bisa dipakai untuk
 * memberi diri sendiri/orang lain peran super_admin, jadi harus dibatasi
 * seketat ScriptSettingsPage (lihat canAccess() di bawah), bukan dibiarkan
 * terbuka untuk siapa pun yang punya akses panel seperti resource konten
 * lain di project ini.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->helperText(fn (string $context) => $context === 'edit' ? 'Kosongkan untuk mempertahankan kata sandi lama.' : null)
                    ->required(fn (string $context) => $context === 'create')
                    ->minLength(8)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->dehydrateStateUsing(fn (string $state) => $state),
                Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->options(fn () => Role::pluck('name', 'id'))
                    ->multiple()
                    ->preload()
                    ->required()
                    ->helperText('Akun tanpa peran sama sekali tidak bisa login ke panel admin.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->separator(',')
                    ->placeholder('Tanpa peran — tidak bisa login'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
