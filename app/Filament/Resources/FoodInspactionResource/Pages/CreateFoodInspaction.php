<?php

namespace App\Filament\Resources\FoodInspactionResource\Pages;

use App\Filament\Resources\FoodInspactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFoodInspaction extends CreateRecord
{
    protected static string $resource = FoodInspactionResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
