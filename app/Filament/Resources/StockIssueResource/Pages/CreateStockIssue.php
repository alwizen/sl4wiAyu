<?php

namespace App\Filament\Resources\StockIssueResource\Pages;

use App\Filament\Resources\StockIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockIssue extends CreateRecord
{
    protected static string $resource = StockIssueResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
