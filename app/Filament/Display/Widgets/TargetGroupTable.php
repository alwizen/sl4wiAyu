<?php

namespace App\Filament\Display\Widgets;

use App\Models\DailyMenuItem;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TargetGroupTable extends BaseWidget
{
    protected static ?int $sort = 2;
 
    // protected int | string | array $columnSpan = 'full';
 
    protected static ?string $heading = 'Penerima Manfaat Hari Ini';
 
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
                // Tables\Columns\TextColumn::make('menu.menu_name')
                //     ->label('Nama Menu'),
                Tables\Columns\TextColumn::make('targetGroup.name')
                    ->label('Target Group'),
                Tables\Columns\TextColumn::make('target_quantity')
                    ->label('Jumlah')
                    ->suffix(' porsi'),
                    // ->summarize(Sum::make()),
            ]);
    }
}