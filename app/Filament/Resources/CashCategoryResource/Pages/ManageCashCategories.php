<?php

namespace App\Filament\Resources\CashCategoryResource\Pages;

use App\Filament\Resources\CashCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCashCategories extends ManageRecords
{
    protected static string $resource = CashCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kategori')
                ->icon('heroicon-o-plus')
                ->color('primary')
        ];
    }
}
