<?php

namespace App\Filament\Widgets;

use App\Models\StockIssue;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class TodayStockIssue extends Widget
{
    use HasWidgetShield;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.today-stock-issue';

    //    protected int | string | array $columnSpan = 6;
    protected static ?int $sort = 1;


    public function render(): \Illuminate\Contracts\View\View
    {
        $issues = StockIssue::with(['items.warehouseItem'])
            ->whereDate('issue_date', now())
            ->latest()
            ->get();

        return view(static::$view, [
            'issues' => $issues,
        ]);
    }
}
