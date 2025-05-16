<?php

namespace App\Filament\Resources\StockReceivingResource\Pages;

use App\Filament\Resources\StockReceivingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockReceiving extends EditRecord
{
    protected static string $resource = StockReceivingResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
