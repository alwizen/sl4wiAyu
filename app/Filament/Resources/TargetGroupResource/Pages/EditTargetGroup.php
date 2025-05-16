<?php

namespace App\Filament\Resources\TargetGroupResource\Pages;

use App\Filament\Resources\TargetGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTargetGroup extends EditRecord
{
    protected static string $resource = TargetGroupResource::class;

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
