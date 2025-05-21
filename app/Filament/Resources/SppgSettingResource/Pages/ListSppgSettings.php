<?php

namespace App\Filament\Resources\SppgSettingResource\Pages;

use App\Filament\Resources\SppgSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSppgSettings extends ListRecords
{
    protected static string $resource = SppgSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
