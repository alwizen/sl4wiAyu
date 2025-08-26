<?php

namespace App\Filament\Resources\SppgPurchaseOrderResource\Pages;

use App\Filament\Resources\SppgPurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSppgPurchaseOrders extends ListRecords
{
    protected static string $resource = SppgPurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Buat PO')
            ->icon('heroicon-o-plus'),
        ];
    }
}
