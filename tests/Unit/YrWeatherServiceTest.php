<?php

use Illuminate\Support\Facades\Cache;
use YourVendor\LaravelYr\Services\YrWeatherService;

beforeEach(function () {
    $this->service = app(YrWeatherService::class);
});

it('can get weather forecast for coordinates', function () {
    $forecast = $this->service->getForecast(59.9139, 10.7522);

    expect($forecast)->toBeArray()
        ->and($forecast)->toHaveKeys(['timeseries', 'updated_at'])
        ->and($forecast['timeseries'])->toBeArray()
        ->and($forecast['timeseries'])->not->toBeEmpty();
});

it('can get current weather for coordinates', function () {
    $weather = $this->service->getCurrentWeather(59.9139, 10.7522);

    expect($weather)->toBeArray()
        ->and($weather)->toHaveKeys(['time', 'temperature', 'wind_speed', 'humidity'])
        ->and($weather['temperature'])->toBeFloat();
});

it('can get weather forecast with altitude', function () {
    $forecast = $this->service->getForecast(59.9139, 10.7522, 90);

    expect($forecast)->toBeArray()
        ->and($forecast['timeseries'])->not->toBeEmpty();
});

it('caches weather forecast data', function () {
    Cache::shouldReceive('remember')
        ->once()
        ->andReturn([
            'timeseries' => [
                [
                    'time' => '2025-11-14T12:00:00Z',
                    'temperature' => 8.5,
                    'wind_speed' => 3.2,
                    'humidity' => 65,
                ],
            ],
            'updated_at' => '2025-11-14T11:00:00Z',
        ]);

    $forecast = $this->service->getForecast(59.9139, 10.7522);

    expect($forecast)->toBeArray();
});

it('returns null on API error', function () {
    $service = new YrWeatherService('InvalidUserAgent', 3600);

    $weather = $service->getCurrentWeather(999, 999);

    expect($weather)->toBeNull();
});

it('formats weather data correctly', function () {
    $forecast = $this->service->getForecast(59.9139, 10.7522);

    expect($forecast['timeseries'][0])
        ->toHaveKeys([
            'time',
            'temperature',
            'feels_like',
            'wind_speed',
            'wind_direction',
            'wind_gust',
            'humidity',
            'pressure',
            'cloud_coverage',
            'dew_point',
            'precipitation_amount',
            'uv_index',
            'symbol_code',
        ]);
});

it('generates correct weather symbol URL', function () {
    $url = $this->service->getSymbolUrl('clearsky_day');

    expect($url)
        ->toBeString()
        ->toContain('api.met.no')
        ->toContain('clearsky_day');
});

it('can use complete endpoint', function () {
    $forecast = $this->service->getForecast(59.9139, 10.7522, null, true);

    expect($forecast)->toBeArray()
        ->and($forecast['timeseries'])->not->toBeEmpty();
});
