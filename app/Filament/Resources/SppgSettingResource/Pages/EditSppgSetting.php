<?php

namespace App\Filament\Resources\SppgSettingResource\Pages;

use App\Filament\Resources\SppgSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSppgSetting extends EditRecord
{
    protected static string $resource = SppgSettingResource::class;

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
