<?php

namespace App\Filament\Widgets;

use App\Models\DailyMenuItem;
use App\Models\NutritionPlanItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DailyMenuToday extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $heading = 'Menu Hari Ini';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->poll('5s')
            ->striped()
            ->query(
                NutritionPlanItem::with('menu')
                    ->whereHas('nutritionPlan', function ($query) {
                        $query->whereDate('nutrition_plan_date', Carbon::today());
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('menu.menu_name')
                    ->label('Menu'),
                Tables\Columns\TextColumn::make('netto')
                    ->label('Netto')
                    ->formatStateUsing(fn($state) => "{$state} gr"),
            ]);
    }
}
