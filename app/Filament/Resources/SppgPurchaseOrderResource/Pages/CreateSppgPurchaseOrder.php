<?php

namespace App\Filament\Resources\SppgPurchaseOrderResource\Pages;

use App\Filament\Resources\SppgPurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSppgPurchaseOrder extends CreateRecord
{
    protected static string $resource = SppgPurchaseOrderResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
