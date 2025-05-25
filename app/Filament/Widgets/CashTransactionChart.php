<?php

namespace App\Filament\Widgets;

use App\Models\CashTransaction;
use Filament\Widgets\ChartWidget;
use Filament\Forms;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashTransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Transaksi Kas';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    public ?string $filter = 'last_30_days';
    
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            'last_7_days' => '7 Hari Terakhir',
            'last_30_days' => '30 Hari Terakhir',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_year' => 'Tahun Ini',
            'last_year' => 'Tahun Lalu',
            'custom' => 'Custom Range',
        ];
    }
    
    public function getFilterForm(): ?array
    {
        return [
            Forms\Components\DatePicker::make('date_from')
                ->label('Tanggal Mulai')
                ->displayFormat('d/m/Y')
                ->default(now()->subDays(30))
                ->visible(fn() => $this->filter === 'custom'),
                
            Forms\Components\DatePicker::make('date_to')
                ->label('Tanggal Akhir')
                ->displayFormat('d/m/Y')
                ->default(now())
                ->visible(fn() => $this->filter === 'custom'),
        ];
    }
    
    protected function getData(): array
    {
        $dateRange = $this->getDateRange();
        
        // Ambil data transaksi berdasarkan rentang tanggal
        $transactions = CashTransaction::with('category')
            ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as expense')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Buat array label dan data
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        // Generate semua tanggal dalam rentang
        $period = new \DatePeriod(
            $dateRange['start'],
            new \DateInterval('P1D'),
            $dateRange['end']->addDay()
        );
        
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            
            $transaction = $transactions->firstWhere('date', $dateString);
            $incomeData[] = $transaction ? (float) $transaction->income : 0;
            $expenseData[] = $transaction ? (float) $transaction->expense : 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
    
    private function getDateRange(): array
    {
        $now = Carbon::now();
        
        return match($this->filter) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
            ],
            'last_7_days' => [
                'start' => $now->copy()->subDays(6)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'last_30_days' => [
                'start' => $now->copy()->subDays(29)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'last_year' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear(),
            ],
            'custom' => [
                'start' => Carbon::parse($this->filterFormData['date_from'] ?? $now->subDays(30)),
                'end' => Carbon::parse($this->filterFormData['date_to'] ?? $now),
            ],
            default => [
                'start' => $now->copy()->subDays(29)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
        };
    }
    
    public function getHeading(): string
    {
        $dateRange = $this->getDateRange();
        $filterName = $this->getFilters()[$this->filter] ?? 'Custom';
        
        if ($this->filter === 'custom') {
            $filterName = $dateRange['start']->format('d/m/Y') . ' - ' . $dateRange['end']->format('d/m/Y');
        }
        
        return 'Grafik Transaksi Kas (' . $filterName . ')';
    }
}