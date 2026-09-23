<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use App\Support\ImageUploads;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Tim';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Anggota Tim';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position')
                    ->label('Jabatan')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('photo_path')
                    ->label('Foto')
                    ->helperText('Wajib. Rekomendasi 800×800px (potret). Gambar besar otomatis dikecilkan ke lebar 800px & dikonversi ke WebP.')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('team')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'team', maxWidth: 800)),
                Textarea::make('bio')
                    ->label('Bio Singkat')
                    ->required()
                    ->rows(3),
                TextInput::make('linkedin_url')
                    ->label('LinkedIn')
                    ->helperText('Opsional. URL profil LinkedIn lengkap (https://...).')
                    ->url()
                    ->maxLength(255)
                    ->rule('starts_with:http://,https://'),
                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->helperText('Angka lebih kecil tampil lebih dulu.')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Anggota nonaktif tidak tampil di halaman Tentang Kami, tapi tetap tersimpan di sini.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Jabatan'),
                TextColumn::make('linkedin_url')
                    ->label('LinkedIn')
                    ->placeholder('—')
                    ->url(fn (?string $state) => $state)
                    ->openUrlInNewTab(),
                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
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
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
