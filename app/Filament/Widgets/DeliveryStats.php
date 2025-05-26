<?php

namespace App\Filament\Widgets;

use App\Models\Delivery;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\Concerns\Has;

class DeliveryStats extends BaseWidget
{
    use HasWidgetShield;
    
    protected ?string $heading = 'Pengiriman Hari Ini';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        
        $today = Carbon::today();

        $deliveriesToday = Delivery::whereDate('delivery_date', $today);

        return [
            BaseWidget\Card::make('Pengiriman Hari Ini', $deliveriesToday->count())
                ->description('Pengiriman hari ini')
                ->descriptionIcon('heroicon-o-truck')
                ->color('warning'),

            BaseWidget\Card::make('Total Porsi', $deliveriesToday->sum('qty') . ' Box')
            ->description('Pengiriman hari ini')
            ->descriptionIcon('heroicon-o-sparkles')
            ->color('primary'),
            

            BaseWidget\Card::make('Selesai / Kembali', (clone $deliveriesToday)->where('status', 'selesai')->count())
                ->description('Pengiriman selesai hari ini')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
