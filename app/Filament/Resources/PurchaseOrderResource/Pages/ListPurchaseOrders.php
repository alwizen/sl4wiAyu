<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Stok Gudang')
                ->label('Stok Gudang')
                ->icon('heroicon-o-list-bullet')
                ->url(route('filament.admin.resources.warehouse-items.index')) 
                ->color('warning')
                ->openUrlInNewTab(),
            Action::make('Penerima Barang')
                ->label('Penerima Barang')
                ->icon('heroicon-o-queue-list')
                ->url(route('filament.admin.resources.stock-receivings.index')) 
                ->color('success')
                ->openUrlInNewTab(),
            Actions\CreateAction::make()
                ->label('Tambah Purchase Order (PO)')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
