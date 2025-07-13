<?php

namespace App\Filament\Widgets;

use App\Models\StockReceiving;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class TodayStockReceiving extends Widget
{
    use HasWidgetShield;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.today-stock-receiving';

    //    protected int | string | array $columnSpan = 6;

    protected static ?int $sort = 2;

    public function render(): \Illuminate\Contracts\View\View
    {
        $receivings = StockReceiving::with(['purchaseOrder', 'stockReceivingItems.warehouseItem'])
            ->whereDate('received_date', now())
            ->latest()
            ->get();

        return view(static::$view, [
            'receivings' => $receivings,
        ]);
    }
}
