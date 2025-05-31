<?php

namespace App\Filament\Pages;

use App\Filament\Display\Widgets\DeliveryStatusTable;
use App\Filament\Resources\DeliveryResource\Widgets\DeliveryStats;
use App\Filament\Widgets\CashTransactionChart;
use App\Filament\Widgets\CashTransactionStats;
use App\Filament\Widgets\DeliveryStats as WidgetsDeliveryStats;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\TodayStockIssue;
use App\Filament\Widgets\TodayStockReceiving;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
    // use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public function getTitle(): string
    {
        $greeting = $this->getGreeting();
        $userName = Auth::user()->name ?? 'User';
        return "{$greeting}, {$userName}! 🎉";
}

    // Atau alternatif menggunakan getHeading() jika ingin lebih fleksibel
    // public function getHeading(): string
    // {
    //     $greeting = $this->getGreeting();
    //     $userName = Auth::user()->name ?? 'User';
    //     return "Dashboard | {$greeting}, {$userName}! 👋🏻";
    // }

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
            CashTransactionStats::class,
            CashTransactionChart::class,
            WidgetsDeliveryStats::class,
            DeliveryStatusTable::class,
            TodayStockReceiving::class,
            TodayStockIssue::class
        ];
    }

    protected static string $view = 'filament.pages.dashboard';
}