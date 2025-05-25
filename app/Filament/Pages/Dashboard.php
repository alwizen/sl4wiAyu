<?php

namespace App\Filament\Pages;

use App\Filament\Display\Widgets\DeliveryStatusTable;
use App\Filament\Widgets\CashTransactionChart;
use App\Filament\Widgets\CashTransactionStats;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\TodayStockIssue;
use App\Filament\Widgets\TodayStockReceiving;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
//    use HasPageShield;
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected function getFooterWidgets(): array
    {
        return [
//            GreetingWidget::class,
            CashTransactionStats::class,
            CashTransactionChart::class,
            DeliveryStatusTable::class,
            TodayStockReceiving::class,
            TodayStockIssue::class

        ];
    }



    protected static string $view = 'filament.pages.dashboard';
}
