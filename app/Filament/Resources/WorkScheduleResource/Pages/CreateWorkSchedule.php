<?php

namespace App\Filament\Resources\WorkScheduleResource\Pages;

use App\Filament\Resources\WorkScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkSchedule extends CreateRecord
{
    protected static string $resource = WorkScheduleResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
