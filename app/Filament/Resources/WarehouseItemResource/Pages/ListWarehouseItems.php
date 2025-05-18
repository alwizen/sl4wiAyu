<?php

namespace App\Filament\Resources\WarehouseItemResource\Pages;

use App\Filament\Resources\WarehouseItemResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWarehouseItems extends ListRecords
{
    protected static string $resource = WarehouseItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->label('Semua Barang'),
            'kering' => Tab::make('Kering')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('warehouse_category_id', 1)),
            'basah' => Tab::make('Basah')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('warehouse_category_id', 2)),
            'bumbu' => Tab::make('Bumbu')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('warehouse_category_id', 3)),
        ];
    }

}
