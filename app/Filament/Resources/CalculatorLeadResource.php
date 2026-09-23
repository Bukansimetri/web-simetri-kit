<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalculatorLeadResource\Pages;
use App\Models\CalculatorLead;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * CMS untuk lead dari form "Hitung Estimasi Penghematan" (home) — terpisah
 * dari ContactSubmissionResource ("Pesan Masuk") karena datanya terstruktur
 * (metode, tagihan/peralatan, hasil hitungan) dan perlu tracking follow-up
 * yang lebih detail (status, catatan, PIC, waktu).
 */
class CalculatorLeadResource extends Resource
{
    protected static ?string $model = CalculatorLead::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Lead Kalkulator';

    protected static ?string $modelLabel = 'Lead Kalkulator';

    protected static ?string $navigationGroup = 'Prospek & Pesan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return [
            CalculatorLead::STATUS_NEW => 'Baru',
            CalculatorLead::STATUS_CONTACTED => 'Sudah Dihubungi',
            CalculatorLead::STATUS_QUALIFIED => 'Qualified',
            CalculatorLead::STATUS_WON => 'Deal',
            CalculatorLead::STATUS_LOST => 'Batal',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusColors(): array
    {
        return [
            CalculatorLead::STATUS_NEW => 'danger',
            CalculatorLead::STATUS_CONTACTED => 'warning',
            CalculatorLead::STATUS_QUALIFIED => 'info',
            CalculatorLead::STATUS_WON => 'success',
            CalculatorLead::STATUS_LOST => 'gray',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Pelanggan')
                    ->schema([
                        Placeholder::make('name')
                            ->label('Nama')
                            ->content(fn (CalculatorLead $record) => $record->name),
                        Placeholder::make('phone')
                            ->label('No. WhatsApp')
                            ->content(fn (CalculatorLead $record) => $record->phone),
                        Placeholder::make('email')
                            ->label('Email')
                            ->content(fn (CalculatorLead $record) => $record->email ?: '-'),
                        Placeholder::make('area')
                            ->label('Area')
                            ->content(fn (CalculatorLead $record) => $record->area ?: '-'),
                        Placeholder::make('created_at')
                            ->label('Waktu Masuk')
                            ->content(fn (CalculatorLead $record) => $record->created_at->translatedFormat('d F Y H:i')),
                    ])
                    ->columns(2),

                Section::make('Input & Hasil Estimasi')
                    ->description('Read-only — hasil dihitung server saat lead ini masuk, memakai asumsi yang berlaku saat itu.')
                    ->schema([
                        Placeholder::make('category')
                            ->label('Kategori')
                            ->content(fn (CalculatorLead $record) => $record->category === 'industrial' ? 'Industrial / Komersial' : 'Residential'),
                        Placeholder::make('method')
                            ->label('Metode Input')
                            ->content(fn (CalculatorLead $record) => $record->method === 'bill' ? 'Berdasarkan Tagihan' : 'Berdasarkan Peralatan'),
                        Placeholder::make('input_detail')
                            ->label('Detail Input')
                            ->content(function (CalculatorLead $record) {
                                if ($record->method === 'bill') {
                                    $lines = ['Tagihan: Rp '.number_format((int) $record->monthly_bill, 0, ',', '.')];

                                    if (filled($record->va_capacity)) {
                                        $lines[] = "Kapasitas: {$record->va_capacity} VA";
                                    }

                                    return implode(' · ', $lines);
                                }

                                return collect($record->appliances ?? [])
                                    ->map(fn (array $a) => "{$a['label']} ×{$a['qty']}")
                                    ->implode(', ') ?: '-';
                            }),
                        Placeholder::make('estimated_monthly_bill')
                            ->label('Tagihan Efektif')
                            ->content(fn (CalculatorLead $record) => 'Rp '.number_format($record->estimated_monthly_bill, 0, ',', '.')),
                        Placeholder::make('savings_year1')
                            ->label('Hemat Tahun 1')
                            ->content(fn (CalculatorLead $record) => 'Rp '.number_format($record->savings_year1, 0, ',', '.')),
                        Placeholder::make('total_savings_25y')
                            ->label('Total Hemat 25 Tahun')
                            ->content(fn (CalculatorLead $record) => 'Rp '.number_format($record->total_savings_25y, 0, ',', '.')),
                        Placeholder::make('breakeven_years')
                            ->label('Breakeven')
                            ->content(fn (CalculatorLead $record) => "{$record->breakeven_years} tahun"),
                        Placeholder::make('annual_kwh')
                            ->label('Produksi Surya Tahunan')
                            ->content(fn (CalculatorLead $record) => "{$record->annual_kwh} kWh"),
                        Placeholder::make('assumptions')
                            ->label('Asumsi yang Berlaku Saat Itu')
                            ->content(function (CalculatorLead $record) {
                                $a = $record->assumptions ?? [];

                                return sprintf(
                                    'Tarif Rp %s/kWh · Cakupan %s%% · Eskalasi %s%%/th · Faktor investasi %s×',
                                    number_format($a['tariff_per_kwh'] ?? 0, 0, ',', '.'),
                                    $a['solar_coverage_percent'] ?? '-',
                                    $a['tariff_escalation_percent'] ?? '-',
                                    $a['investment_factor'] ?? '-',
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Follow-up')
                    ->description('Satu-satunya bagian yang bisa diubah dari sini.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(self::statusOptions())
                            ->required(),
                        Placeholder::make('followed_up_by')
                            ->label('PIC Terakhir')
                            ->content(fn (CalculatorLead $record) => $record->followedUpBy?->name ?: '-'),
                        DateTimePicker::make('followed_up_at')
                            ->label('Waktu Follow-up Terakhir')
                            ->native(false),
                        Textarea::make('follow_up_notes')
                            ->label('Catatan Follow-up')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Hasil telepon/kunjungan, kebutuhan spesifik pelanggan, dsb.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('No. WhatsApp')
                    ->searchable(),
                TextColumn::make('area')
                    ->label('Area')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state) => $state === 'industrial' ? 'Industrial' : 'Residential')
                    ->badge(),
                TextColumn::make('savings_year1')
                    ->label('Hemat/Tahun')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state) => self::statusColors()[$state] ?? 'gray')
                    ->badge(),
                TextColumn::make('followedUpBy.name')
                    ->label('PIC')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions()),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'residential' => 'Residential',
                        'industrial' => 'Industrial',
                    ]),
                Filter::make('belum_follow_up')
                    ->label('Belum Di-follow-up')
                    ->query(fn (Builder $query) => $query->where('status', CalculatorLead::STATUS_NEW))
                    ->toggle(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('dari')
                            ->native(false),
                        DatePicker::make('sampai')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['sampai'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Action::make('tandai_dihubungi')
                    ->label('Tandai Sudah Dihubungi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CalculatorLead $record) => $record->status === CalculatorLead::STATUS_NEW)
                    ->requiresConfirmation()
                    ->action(function (CalculatorLead $record) {
                        $record->update([
                            'status' => CalculatorLead::STATUS_CONTACTED,
                            'followed_up_by_id' => Auth::id(),
                            'followed_up_at' => now(),
                        ]);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalculatorLeads::route('/'),
            'edit' => Pages\EditCalculatorLead::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return strval(static::getModel()::where('status', CalculatorLead::STATUS_NEW)->count()) ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
