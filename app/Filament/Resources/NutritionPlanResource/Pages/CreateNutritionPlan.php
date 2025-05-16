<?php

namespace App\Filament\Resources\NutritionPlanResource\Pages;

use App\Filament\Resources\NutritionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNutritionPlan extends CreateRecord
{
    protected static string $resource = NutritionPlanResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
