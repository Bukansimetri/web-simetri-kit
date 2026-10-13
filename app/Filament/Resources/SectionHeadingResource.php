<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionHeadingResource\Pages;
use App\Models\SectionHeading;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SectionHeadingResource extends Resource
{
    protected static ?string $model = SectionHeading::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Judul Section';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Judul Section';

    protected static ?string $pluralModelLabel = 'Judul Section';

    protected static ?string $slug = 'judul-section';

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
        return $form->schema([
            TextInput::make('section')
                ->label('Section')
                ->formatStateUsing(fn (?string $state): string => static::sectionLabel($state))
                ->disabled()
                ->dehydrated(false),
            Textarea::make('title')->label('Judul')->required()->maxLength(255)->rows(2),
            Textarea::make('subtitle')->label('Subjudul')->maxLength(500)->rows(3),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('section')
            ->paginated(false)
            ->columns([
                TextColumn::make('section')
                    ->label('Section')
                    ->formatStateUsing(fn (?string $state): string => static::sectionLabel($state))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('section', 'like', "%{$search}%")),
                TextColumn::make('title')->label('Judul')->limit(70),
                TextColumn::make('subtitle')->label('Subjudul')->limit(80)->placeholder('—'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([]);
    }

    private static function sectionLabel(?string $section): string
    {
        $labels = [
            'beranda.mengapa-beralih' => 'Beranda – Mengapa Beralih', 'beranda.cara-kerja' => 'Beranda – Cara Kerja',
            'beranda.kalkulator' => 'Beranda – Kalkulator', 'beranda.testimoni' => 'Beranda – Testimoni',
            'beranda.solusi' => 'Beranda – Solusi Produk', 'tentang-kami.tim' => 'Tentang Kami – Tim',
            'tentang-kami.testimoni' => 'Tentang Kami – Testimoni', 'tentang-kami.logo-klien' => 'Tentang Kami – Logo Klien',
            'karir.mengapa-bergabung' => 'Karir – Mengapa Bergabung', 'karir.proses-rekrutmen' => 'Karir – Proses Rekrutmen',
            'tentang-kami.misi' => 'Tentang Kami – Misi', 'tentang-kami.nilai' => 'Tentang Kami – Nilai', 'tentang-kami.trust-strip' => 'Tentang Kami – Trust Strip',
            'karir.lowongan' => 'Karir – Posisi Terbuka', 'produk.faq' => 'Produk – FAQ',
            'faq.daftar' => 'FAQ – Daftar Pertanyaan', 'artikel.daftar' => 'Artikel – Daftar Artikel',
            'artikel.newsletter' => 'Artikel – Newsletter', 'produk-detail.spesifikasi' => 'Detail Produk – Spesifikasi',
            'produk-detail.terkait' => 'Detail Produk – Produk Terkait', 'artikel-detail.produk-terkait' => 'Detail Artikel – Produk Terkait',
            'artikel-detail.terbaru' => 'Detail Artikel – Artikel Terbaru', 'artikel-detail.artikel-terkait' => 'Detail Artikel – Artikel Terkait',
            'kontak.form' => 'Kontak – Formulir', 'kontak.faq' => 'Kontak – FAQ', 'portofolio.daftar' => 'Portofolio – Daftar Proyek',
        ];

        return $labels[$section ?? ''] ?? $section ?? '—';
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSectionHeadings::route('/'), 'edit' => Pages\EditSectionHeading::route('/{record}/edit')];
    }
}
