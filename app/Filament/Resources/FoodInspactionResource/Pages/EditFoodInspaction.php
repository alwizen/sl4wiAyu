<?php

namespace App\Filament\Resources\FoodInspactionResource\Pages;

use App\Filament\Resources\FoodInspactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFoodInspaction extends EditRecord
{
    protected static string $resource = FoodInspactionResource::class;

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
