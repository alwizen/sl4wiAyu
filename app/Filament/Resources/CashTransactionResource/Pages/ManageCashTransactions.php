<?php

namespace App\Filament\Resources\CashTransactionResource\Pages;

use App\Filament\Resources\CashTransactionResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;

class ManageCashTransactions extends ManageRecords
{
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
}
