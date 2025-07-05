<?php

namespace App\Providers;

use App\Models\StockReceiving;
use App\Models\User;
use App\Observers\StockReceivingObserver;
use App\Policies\ActivityPolicy;
use Filament\Support\Facades\FilamentView;
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
    }
}
