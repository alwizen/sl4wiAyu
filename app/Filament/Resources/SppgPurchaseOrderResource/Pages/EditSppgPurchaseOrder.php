<?php

namespace App\Filament\Resources\SppgPurchaseOrderResource\Pages;

use App\Filament\Resources\SppgPurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSppgPurchaseOrder extends EditRecord
{
    protected static string $resource = SppgPurchaseOrderResource::class;

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
