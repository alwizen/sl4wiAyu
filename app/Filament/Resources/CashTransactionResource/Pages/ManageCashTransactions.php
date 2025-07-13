<?php

namespace App\Filament\Resources\CashTransactionResource\Pages;

use App\Filament\Resources\CashTransactionResource;
use App\Filament\Resources\CashTransactionResource\Widgets\TransactionStat;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ManageRecords;

class ManageCashTransactions extends ManageRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Kategoty Transaksi')
                ->label('Kategori Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                ->url(route('filament.admin.resources.cash-categories.index')),
            Actions\CreateAction::make()
                ->label('Buat Transaksi Kas')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionStat::class
        ];
    }
}
