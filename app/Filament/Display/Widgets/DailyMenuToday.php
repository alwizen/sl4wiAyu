<?php

namespace App\Filament\Widgets;

use App\Models\DailyMenuItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DailyMenuToday extends BaseWidget
{
    protected static ?int $sort = 1;

    // protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Menu Hari Ini';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->poll('5s')
            ->striped()
            ->query(
                DailyMenuItem::query()
                    ->with(['menu', 'targetGroup'])
                    ->whereHas('dailyMenu', function ($query) {
                        $query->whereDate('menu_date', today());
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('menu.menu_name')
                    ->label('Nama Menu'),
                // Tables\Columns\TextColumn::make('nuttritionPlan.netto')
                //     ->label('Target Group'),
                // Tables\Columns\TextColumn::make('target_quantity')
                //     ->label('Jumlah')
                //     ->suffix(' porsi'),
            ]);
    }
}

