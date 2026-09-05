<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\ArticleCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Artikel';

    protected static ?string $navigationGroup = 'Blog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, ?string $old, Get $get) {
                                // Auto-generate slug dari judul (FR-005) — hanya kalau slug
                                // belum diisi manual berbeda dari hasil slug judul sebelumnya.
                                if (blank($get('slug')) || $get('slug') === Str::slug($old ?? '')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Otomatis dari judul — bisa diubah manual.'),
                        Select::make('article_category_id')
                            ->label('Kategori')
                            ->relationship('articleCategory', 'name')
                            ->options(fn () => ArticleCategory::orderBy('order')->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('redaksi')
                            ->label('Redaksi')
                            ->helperText('Nama penulis/tim penulis (opsional) — ditampilkan sebagai byline, bukan akun admin.'),
                    ])
                    ->columns(2),
                Section::make('Konten')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->helperText('Ditampilkan di kartu artikel & meta deskripsi.')
                            ->required()
                            ->rows(2),
                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required(),
                    ]),
                Section::make('Status Publikasi')
                    ->schema([
                        Radio::make('publish_status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'now' => 'Publish sekarang',
                                'schedule' => 'Jadwalkan',
                            ])
                            ->default('draft')
                            ->live()
                            ->afterStateHydrated(function (Radio $component, $record) {
                                if (! $record || $record->published_at === null) {
                                    $component->state('draft');
                                } elseif ($record->published_at->isFuture()) {
                                    $component->state('schedule');
                                } else {
                                    $component->state('now');
                                }
                            }),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publish')
                            ->visible(fn (Get $get) => $get('publish_status') === 'schedule')
                            ->required(fn (Get $get) => $get('publish_status') === 'schedule'),
                    ]),
            ]);
    }

    /**
     * Terjemahkan pilihan `publish_status` (Draft/Publish sekarang/Jadwalkan)
     * jadi nilai `published_at` yang sesungguhnya disimpan (FR-009, FR-010;
     * data-model.md § Status turunan) — dipanggil dari Create/Edit page hooks.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutatePublishStatus(array $data): array
    {
        $status = $data['publish_status'] ?? 'draft';

        $data['published_at'] = match ($status) {
            'now' => now(),
            'schedule' => $data['published_at'] ?? null,
            default => null,
        };

        unset($data['publish_status']);

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('articleCategory.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->label('Tanggal Publish')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Draft'),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
