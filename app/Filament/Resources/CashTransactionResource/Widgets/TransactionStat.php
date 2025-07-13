<?php

namespace App\Filament\Resources\CashTransactionResource\Widgets;

use App\Filament\Resources\CashTransactionResource\Pages\ManageCashTransactions;
use App\Models\CashTransaction;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStat extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ManageCashTransactions::class;
    }

    protected function getStats(): array
    {
        // Get the filtered query from the table
        $query = $this->getPageTableQuery();

        // Calculate income statistics
        $incomeQuery = (clone $query)->whereHas('category', function ($q) {
            $q->where('type', 'income');
        });

        $totalIncome = $incomeQuery->sum('amount');
        $countIncome = $incomeQuery->count();

        // Calculate expense statistics
        $expenseQuery = (clone $query)->whereHas('category', function ($q) {
            $q->where('type', 'expense');
        });

        $totalExpense = $expenseQuery->sum('amount');
        $countExpense = $expenseQuery->count();

        return [
            Stat::make('Jumlah Transaksi Pemasukan', number_format($countIncome, 0, ',', '.'))
                ->description('Total transaksi pemasukan')
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('success'),

            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description('Total nilai pemasukan')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Jumlah Transaksi Pengeluaran', number_format($countExpense, 0, ',', '.'))
                ->description('Total transaksi pengeluaran')
                ->descriptionIcon('heroicon-o-minus-circle')
                ->color('danger'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalExpense, 0, ',', '.'))
                ->description('Total nilai pengeluaran')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
