<?php

namespace App\Providers;

use App\Filament\Resources\SppgPurchaseOrderResource\Api\SppgPurchaseOrderApiService;
use App\Models\Payroll;
use App\Models\StockReceiving;
use App\Models\User;
use App\Observers\PayrollObserver;
use App\Observers\StockReceivingObserver;
use App\Policies\ActivityPolicy;
use Filament\Support\Facades\FilamentView;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Contracts\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        parent::register();
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/app.js')"));
    }

    /**
     * Bootstrap any application services.
     */
    //    public function boot(): void
    //    {
    //        //
    //        Gate::define('viewApiDocs', function (User $user) {
    //            return true;
    //        });
    //        // Gate::policy()
    //        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
    //            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
    //        });
    //    }

    public function boot(): void
    {
        // Memastikan helper setting.php dimuat
        require_once app_path('Helpers/setting.php');
        Gate::policy(Activity::class, ActivityPolicy::class);
        StockReceiving::observe(StockReceivingObserver::class);
        // Payroll::observe(PayrollObserver::class);

        // $this->registerSppgApiRoutes();
    }

    // private function registerSppgApiRoutes()
    // {
    //     Route::prefix('api')
    //         ->middleware(['api'])
    //         ->group(function () {
    //             // Standard CRUD routes
    //             Route::apiResource('sppg-purchase-orders', \App\Http\Controllers\Api\SppgPurchaseOrderController::class);

    //             // Custom actions
    //             Route::post('sppg-purchase-orders/{sppg_purchase_order}/submit', [\App\Http\Controllers\Api\SppgPurchaseOrderController::class, 'submit']);
    //             Route::post('sppg-purchase-orders/{sppg_purchase_order}/reopen', [\App\Http\Controllers\Api\SppgPurchaseOrderController::class, 'reopen']);

    //             // Stats endpoint
    //             Route::get('sppg-purchase-orders-stats', [\App\Http\Controllers\Api\SppgPurchaseOrderController::class, 'stats']);
    //         });
    // }
}
