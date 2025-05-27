<?php

namespace App\Filament\Resources\DailyMenuResource\Pages;

use App\Filament\Resources\DailyMenuResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListDailyMenus extends ListRecords
{
    protected static string $resource = DailyMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Nutrisi Harian')
                ->label('Nutrisi Harian')
                ->icon('heroicon-o-calculator')
                ->url(route('filament.admin.resources.nutrition-plans.index'))
                ->color('warning')
                ->openUrlInNewTab(),
            Actions\CreateAction::make()
                ->label('Tambah Menu Harian')
                ->icon('heroicon-o-plus')
                ->color('primary'),

        ];
    }
}
