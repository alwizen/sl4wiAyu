<?php

namespace App\Filament\Resources\StockReceivingResource\Pages;

use App\Filament\Resources\StockReceivingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockReceivings extends ListRecords
{
    protected static string $resource = StockReceivingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Penerimaan PO')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
