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

Route::prefix($prefix)->group(function () use ($currentEndpoint, $forecastEndpoint) {
    Route::get($currentEndpoint, [WeatherController::class, 'current'])->name('yr.api.current');
    Route::get($forecastEndpoint, [WeatherController::class, 'forecast'])->name('yr.api.forecast');
});
