<?php

namespace App\Filament\Resources\NutritionPlanResource\Pages;

use App\Filament\Resources\NutritionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\ExportAction;

class ListNutritionPlans extends ListRecords
{
    protected static string $resource = NutritionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Lihat Menu Harian')
                ->label('Lihat Menu Harian')
                ->icon('heroicon-o-calendar')
                ->url(route('filament.admin.resources.daily-menus.index')) // Sesuaikan dengan nama resource tujuan
                ->color('success')
                ->openUrlInNewTab(),

            Actions\CreateAction::make()
                ->label('Tambah Rencana Nutrisi')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
