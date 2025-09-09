<?php

namespace App\Filament\Pages;

use App\Filament\Display\Widgets\DeliveryStatusTable;
use App\Filament\Resources\DeliveryResource\Widgets\DeliveryStats;
use App\Filament\Widgets\CashTransactionChart;
use App\Filament\Widgets\CashTransactionStats;
use App\Filament\Widgets\DeliveryStats as WidgetsDeliveryStats;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\StatusDeliveryTabel;
use App\Filament\Widgets\TodayStockIssue;
use App\Filament\Widgets\TodayStockReceiving;
use App\Filament\Widgets\WarehouseCategoryChart;
use App\Filament\Widgets\WarehouseDryChart;
use App\Filament\Widgets\WarehouseStockChart;
use App\Filament\Widgets\WarehouseWetChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    // use HasPageShield;

    protected static ?string $navigationGroup = 'Ringkasan';

    protected static ?string $navigationLabel = 'Beranda';

    protected static ?string $navigationIcon = 'heroicon-m-chart-pie';

    public function getTitle(): string
    {
        $greeting = $this->getGreeting();
        $userName = Auth::user()->name ?? 'User';
        return "{$greeting}, {$userName}! ✨";
    }

    private function getGreeting(): string
    {
        $hour = Carbon::now()->hour;

        if ($hour >= 0 && $hour < 12) {
            return 'Selamat Pagi';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'Selamat Sore';
        } else {
            return 'Selamat Malam';
        }
    }

    protected function getFooterWidgets(): array
    {
        return [
            // GreetingWidget::class,
            // DeliveryStats::class,
            // AccountWidget::class,
            // WarehouseStockChart::class,
            // WarehouseWetChart::class,
            // WarehouseDryChart::class,
            // TodayStockReceiving::class,
            // TodayStockIssue::class,
            WidgetsDeliveryStats::class,
            StatusDeliveryTabel::class,
            CashTransactionStats::class,
            CashTransactionChart::class,
        ];
    }

    protected static string $view = 'filament.pages.dashboard';
}
