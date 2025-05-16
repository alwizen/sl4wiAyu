<?php

namespace App\Filament\Resources\TargetGroupResource\Pages;

use App\Filament\Resources\TargetGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTargetGroup extends CreateRecord
{
    protected static string $resource = TargetGroupResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
