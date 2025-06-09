<?php

namespace App\Filament\Resources\StockIssueResource\Pages;

use App\Filament\Resources\StockIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockIssues extends ListRecords
{
    protected static string $resource = StockIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Permintaan ahan mask)')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
