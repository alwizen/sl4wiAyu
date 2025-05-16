<?php

namespace App\Filament\Resources\NutrientResource\Pages;

use App\Filament\Resources\NutrientResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNutrients extends ManageRecords
{
    protected static string $resource = NutrientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
