<?php

namespace App\Providers\Filament;

use App\Filament\Display\Widgets\DeliveryStatusTable;
use App\Filament\Display\Widgets\MenuDisplayWidget;
use App\Filament\Display\Widgets\TargetGroupTable;
use App\Filament\Widgets\DailyMenuToday;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DisplayPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('display')
            ->path('display')
            ->brandLogo(asset('images/1.svg'))
            ->brandLogoHeight('3.5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->login()
            // ->sidebarFullyCollapsibleOnDesktop()
            ->navigation(false)
            // ->topbar(false)
            ->discoverResources(in: app_path('Filament/Display/Resources'), for: 'App\\Filament\\Display\\Resources')
            ->discoverPages(in: app_path('Filament/Display/Pages'), for: 'App\\Filament\\Display\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Display/Widgets'), for: 'App\\Filament\\Display\\Widgets')
            ->widgets([
                DailyMenuToday::class,
                TargetGroupTable::class,
                DeliveryStatusTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
