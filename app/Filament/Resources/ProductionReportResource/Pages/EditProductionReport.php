<?php

namespace App\Filament\Resources\ProductionReportResource\Pages;

use App\Filament\Resources\ProductionReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionReport extends EditRecord
{
    protected static string $resource = ProductionReportResource::class;

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
