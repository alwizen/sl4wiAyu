<?php

namespace App\Filament\Widgets;

use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class WarehouseStockChart extends ChartWidget
{
    use HasWidgetShield;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Stok Bahan Baku Gudang';

    protected function getType(): string
    {
        return 'bar';
    }

    // Dropdown filter di pojok widget
    protected function getFilters(): ?array
    {
        // key = id kategori (atau 'all'), value = label yang tampil
        $categories = WarehouseCategory::orderBy('name')->pluck('name', 'id')->toArray();

        return ['all' => 'Semua'] + $categories; // 'Semua' + tiap kategori
    }

    protected function getData(): array
    {
        $selected = $this->filter ?? 'all';

        // Warna-warna untuk dataset per kategori
        $palette = [
            '#60a5fa',
            '#34d399',
            '#fbbf24',
            '#a78bfa',
            '#f87171',
            '#10b981',
            '#818cf8',
            '#f472b6',
            '#facc15',
            '#2dd4bf',
            '#c084fc',
            '#fb7185',
        ];

        // Jika user pilih satu kategori → tampilkan 1 dataset
        if ($selected !== 'all') {
            $category = WarehouseCategory::find($selected);

            if (! $category) {
                return ['datasets' => [[]], 'labels' => []];
            }

            $items = WarehouseItem::where('warehouse_category_id', $category->id)
                ->orderBy('name')
                ->get(['name', 'stock']);

            return [
                'datasets' => [[
                    'label' => 'Stok ' . $category->name,
                    'data' => $items->pluck('stock')->map(fn($v) => (float) $v)->toArray(),
                    'backgroundColor' => $palette[0],
                ]],
                'labels' => $items->pluck('name')->toArray(),
            ];
        }

        // ====== Mode "Semua": dataset per kategori, label = union semua item ======
        $categories = WarehouseCategory::orderBy('name')->get(['id', 'name']);

        // Ambil semua item sekaligus, grupkan per kategori, dan siapkan label union
        $allItems = WarehouseItem::orderBy('name')->get(['name', 'stock', 'warehouse_category_id']);
        $labels   = $allItems->pluck('name')->unique()->values(); // seluruh bahan baku (union nama)

        $itemsByCat = $allItems->groupBy('warehouse_category_id');

        $datasets = [];
        foreach ($categories as $idx => $cat) {
            $map = ($itemsByCat[$cat->id] ?? collect())->pluck('stock', 'name'); // name => stock

            $datasets[] = [
                'label' => $cat->name,
                'data'  => $labels->map(fn($name) => (float) ($map[$name] ?? 0))->toArray(),
                'backgroundColor' => $palette[$idx % count($palette)],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => $labels->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'ticks' => ['autoSkip' => true, 'maxRotation' => 0],
                ],
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
