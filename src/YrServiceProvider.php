<?php

namespace YourVendor\LaravelYr;

use Illuminate\Support\ServiceProvider;
use YourVendor\LaravelYr\Services\GeocodingService;
use YourVendor\LaravelYr\Services\YrWeatherService;
use YourVendor\LaravelYr\View\Components\ForecastCard;
use YourVendor\LaravelYr\View\Components\WeatherCard;

class YrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/yr.php', 'yr'
        );

        $this->app->singleton(YrWeatherService::class, function ($app) {
            return new YrWeatherService(
                config('yr.user_agent'),
                config('yr.cache_ttl')
            );
        });

        $this->app->singleton(GeocodingService::class, function ($app) {
            return new GeocodingService(
                config('yr.user_agent')
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/yr.php' => config_path('yr.php'),
            ], 'yr-config');

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/laravel-yr'),
            ], 'yr-views');

            $this->publishes([
                __DIR__.'/resources/symbols' => public_path('vendor/laravel-yr/symbols'),
            ], 'yr-symbols');
        }

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-yr');

        $this->loadViewComponentsAs('yr', [
            WeatherCard::class,
            ForecastCard::class,
        ]);

        // Register demo route if enabled
        if (config('yr.enable_demo_route', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
