<?php

namespace App\Filament\Resources\ProductionReportResource\Pages;

use App\Filament\Resources\ProductionReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionReport extends CreateRecord
{
    protected static string $resource = ProductionReportResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
