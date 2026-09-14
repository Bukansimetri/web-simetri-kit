<?php

namespace App\Filament\Resources\CalculatorLeadResource\Pages;

use App\Filament\Resources\CalculatorLeadResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

class ListCalculatorLeads extends ListRecords
{
    protected static string $resource = CalculatorLeadResource::class;

    /**
     * Tidak ada CreateAction — lead hanya masuk lewat form kalkulator publik,
     * admin tidak membuatnya manual dari panel. Sebagai gantinya disediakan
     * export CSV atas data yang sedang ditampilkan (mengikuti filter aktif).
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $rows = $this->getFilteredTableQuery()->get();

                    $csv = fopen('php://temp', 'w+');
                    fputcsv($csv, [
                        'Nama', 'WhatsApp', 'Email', 'Area', 'Kategori', 'Metode',
                        'Tagihan Efektif', 'Hemat Tahun 1', 'Total Hemat 25 Tahun',
                        'Breakeven (tahun)', 'Status', 'PIC', 'Waktu Follow-up', 'Waktu Masuk',
                    ]);

                    foreach ($rows as $row) {
                        fputcsv($csv, [
                            $row->name,
                            $row->phone,
                            $row->email,
                            $row->area,
                            $row->category,
                            $row->method,
                            $row->estimated_monthly_bill,
                            $row->savings_year1,
                            $row->total_savings_25y,
                            $row->breakeven_years,
                            $row->status,
                            $row->followedUpBy?->name,
                            $row->followed_up_at?->format('Y-m-d H:i'),
                            $row->created_at->format('Y-m-d H:i'),
                        ]);
                    }

                    rewind($csv);
                    $content = stream_get_contents($csv);
                    fclose($csv);

                    return Response::make($content, 200, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="lead-kalkulator-'.now()->format('Ymd-His').'.csv"',
                    ]);
                }),
        ];
    }
}
