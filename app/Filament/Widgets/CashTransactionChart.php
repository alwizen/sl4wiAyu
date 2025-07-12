<?php

namespace App\Filament\Widgets;

use App\Models\CashTransaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Forms;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashTransactionChart extends ChartWidget
{
    use HasWidgetShield;
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
        $incomeTransactions = CashTransaction::with('category')
            ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
            ->whereHas('category', function ($query) {
                $query->where('type', 'income');
            })
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $expenseTransactions = CashTransaction::with('category')
            ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
            ->whereHas('category', function ($query) {
                $query->where('type', 'expense');
            })
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Hitung saldo awal (sebelum periode yang dipilih)
        $totalIncomeBeforePeriod = CashTransaction::whereHas('category', function ($query) {
            $query->where('type', 'income');
        })
            ->where('transaction_date', '<', $dateRange['start'])
            ->sum('amount');

        $totalExpenseBeforePeriod = CashTransaction::whereHas('category', function ($query) {
            $query->where('type', 'expense');
        })
            ->where('transaction_date', '<', $dateRange['start'])
            ->sum('amount');

        $initialBalance = $totalIncomeBeforePeriod - $totalExpenseBeforePeriod;

        // Buat array label dan data
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        $balanceData = [];

        // Generate semua tanggal dalam rentang
        $period = new \DatePeriod(
            $dateRange['start'],
            new \DateInterval('P1D'),
            $dateRange['end']->addDay()
        );

        $runningBalance = $initialBalance;

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');

            $dailyIncome = $incomeTransactions->has($dateString) ? (float) $incomeTransactions[$dateString]->total : 0;
            $dailyExpense = $expenseTransactions->has($dateString) ? (float) $expenseTransactions[$dateString]->total : 0;

            $incomeData[] = $dailyIncome;
            $expenseData[] = $dailyExpense;

            // Update running balance
            $runningBalance = $runningBalance + $dailyIncome - $dailyExpense;
            $balanceData[] = $runningBalance;
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
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Saldo',
                    'data' => $balanceData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
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
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Pemasukan & Pengeluaran (Rp)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Saldo (Rp)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
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

        return match ($this->filter) {
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
