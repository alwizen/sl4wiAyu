<?php

namespace App\Filament\Resources\NutritionPlanResource\Pages;

use App\Filament\Resources\NutritionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNutritionPlan extends EditRecord
{
    protected static string $resource = NutritionPlanResource::class;

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
