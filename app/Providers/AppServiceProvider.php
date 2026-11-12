<?php

namespace App\Providers;

use App\Support\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        View::share('site', $this->app->make(SiteSettings::class));

        RateLimiter::for('contact', fn (Request $request): array => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perHour(20)->by($request->ip()),
        ]);
    }
}
