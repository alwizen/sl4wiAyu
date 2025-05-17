<?php

namespace App\Filament\Widgets;

use App\Models\DailyMenuItem;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DailyMenuToday extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getTableQuery(): Builder
    {
        return DailyMenuItem::with('dailyMenuItems')->whereHas('dailyMenu', function ($q) {
            $q->whereDate('menu_date', today());
        });
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('dailyMenuItems.menu_name')->label('Nama Menu'),
        ];
    }
}
