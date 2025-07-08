<?php

namespace App\Filament\Resources\FoodInspactionResource\Pages;

use App\Filament\Resources\FoodInspactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFoodInspactions extends ListRecords
{
    protected static string $resource = FoodInspactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pemerikasaan Makanan')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
