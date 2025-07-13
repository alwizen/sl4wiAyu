<?php

namespace App\Filament\Widgets;

use App\Models\CashTransaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;


class CashTransactionStats extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Statistik Transaksi Kas';

    protected static bool $isLazy = false;

    protected function getCards(): array
    {
        $today = Carbon::today();

        // Pemasukan hari ini
        $totalIncomeToday = CashTransaction::whereHas('category', fn($q) =>
        $q->where('type', 'income'))
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        // Pengeluaran hari ini
        $totalExpenseToday = CashTransaction::whereHas('category', fn($q) =>
        $q->where('type', 'expense'))
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        // Saldo total sampai hari ini
        $totalIncomeAll = CashTransaction::whereHas('category', fn($q) =>
        $q->where('type', 'income'))
            ->whereDate('transaction_date', '<=', $today)
            ->sum('amount');

        $totalExpenseAll = CashTransaction::whereHas('category', fn($q) =>
        $q->where('type', 'expense'))
            ->whereDate('transaction_date', '<=', $today)
            ->sum('amount');

        $balance = $totalIncomeAll - $totalExpenseAll;

        return [
            Stat::make('Pemasukan Hari Ini', 'Rp ' . number_format($totalIncomeToday, 0, ',', '.'))
                ->description('Total pemasukan hari ini')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($totalExpenseToday, 0, ',', '.'))
                ->description('Total pengeluaran hari ini')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('Saldo Hari Ini', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description('Total saldo hingga hari ini')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($balance >= 0 ? 'primary' : 'danger'),
        ];
    }
}
