<?php

namespace App\Filament\Widgets;

use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\PieChartWidget;

class WarehouseDryChart extends ChartWidget
{
    use HasWidgetShield;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Stok Gudang Kering';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $category = WarehouseCategory::where('name', 'Kering')->first();

        if (!$category) {
            return [
                'datasets' => [[]],
                'labels' => [],
            ];
        }

        $items = WarehouseItem::where('warehouse_category_id', $category->id)->get();

        return [
            'datasets' => [
                [
                    'label' => 'Stok ' . $category->name,
                    'data' => $items->pluck('stock'),
                    'backgroundColor' => [
                        '#60a5fa',
                        '#fbbf24',
                        '#34d399',
                        '#a78bfa',
                        '#f87171',
                        '#10b981',
                        '#818cf8',
                        '#f472b6',
                        '#facc15',
                        '#2dd4bf',
                        '#c084fc',
                        '#fb7185'
                    ],
                ],
            ],
            'labels' => $items->pluck('name')->toArray(),
        ];
    }
}
