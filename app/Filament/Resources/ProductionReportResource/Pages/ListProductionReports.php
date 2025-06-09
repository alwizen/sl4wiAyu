<?php

namespace App\Filament\Resources\ProductionReportResource\Pages;

use App\Filament\Resources\ProductionReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionReports extends ListRecords
{
    protected static string $resource = ProductionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Buat Laporan Produksi')
            ->icon('heroicon-o-plus'),
        ];
    }
}
