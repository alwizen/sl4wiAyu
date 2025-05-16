<?php

namespace App\Filament\Resources\StockReceivingResource\Pages;

use App\Filament\Resources\StockReceivingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockReceiving extends CreateRecord
{
    protected static string $resource = StockReceivingResource::class;
    
    // Konfigurasi redirect setelah membuat record
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}