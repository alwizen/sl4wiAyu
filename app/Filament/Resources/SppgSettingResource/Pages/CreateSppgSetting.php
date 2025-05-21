<?php

namespace App\Filament\Resources\SppgSettingResource\Pages;

use App\Filament\Resources\SppgSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSppgSetting extends CreateRecord
{
    protected static string $resource = SppgSettingResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
