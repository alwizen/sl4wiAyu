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

            Actions\CreateAction::make()
                ->label('Tambah Purchase Order (PO)')
                ->icon('heroicon-o-plus')
                ->color('primary'),
            Action::make('Stok Gudang')
                ->label('Stok Gudang')
                ->icon('heroicon-o-archive-box')
                ->url(route('filament.admin.resources.warehouse-items.index')) // Sesuaikan dengan nama resource tujuan
                ->color('warning')
                ->openUrlInNewTab(),
            Action::make('Penerima Barang')
                ->label('Penerima Barang')
                ->icon('heroicon-o-truck')
                ->url(route('filament.admin.resources.stock-receivings.index')) // Sesuaikan dengan nama resource tujuan
                ->color('success')
                ->openUrlInNewTab(),
        ];
    }
}
