<?php

use Ekstremedia\LaravelYr\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laravel Yr API Routes
|--------------------------------------------------------------------------
|
| These routes provide RESTful API endpoints for weather data.
| They can be fully customized via configuration:
|
| - YR_API_ROUTES: Enable/disable routes
| - YR_API_ROUTE_PREFIX: Route prefix (default: 'api/weather')
| - YR_API_CURRENT_ENDPOINT: Current weather endpoint (default: 'current')
| - YR_API_FORECAST_ENDPOINT: Forecast endpoint (default: 'forecast')
|
*/

$prefix = config('yr.api_route_prefix', 'api/weather');
$currentEndpoint = config('yr.api_current_endpoint', 'current');
$forecastEndpoint = config('yr.api_forecast_endpoint', 'forecast');
$sunEndpoint = config('yr.api_sun_endpoint', 'sun');
$moonEndpoint = config('yr.api_moon_endpoint', 'moon');

Route::prefix($prefix)->group(function () use ($currentEndpoint, $forecastEndpoint, $sunEndpoint, $moonEndpoint) {
    Route::get($currentEndpoint, [WeatherController::class, 'current'])->name('yr.api.current');
    Route::get($forecastEndpoint, [WeatherController::class, 'forecast'])->name('yr.api.forecast');
    Route::get($sunEndpoint, [WeatherController::class, 'sun'])->name('yr.api.sun');
    Route::get($moonEndpoint, [WeatherController::class, 'moon'])->name('yr.api.moon');
});
