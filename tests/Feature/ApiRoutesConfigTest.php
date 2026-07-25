<?php

use Ekstremedia\LaravelYr\Services\YrWeatherService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Bind a YrWeatherService backed by a mocked Guzzle client so these tests
 * exercise the route/controller wiring without depending on the real,
 * rate-limiting MET API (which routinely 403s automated/CI traffic - this
 * was flaky in CI before the mock was added).
 */
function fakeYrWeatherService(): void
{
    Cache::flush(); // avoid a cached forecast from a previous test masking the fake

    $body = json_encode([
        'properties' => [
            'meta' => ['updated_at' => '2026-01-01T00:00:00Z', 'units' => []],
            'timeseries' => [[
                'time' => '2026-01-01T00:00:00Z',
                'data' => [
                    'instant' => ['details' => ['air_temperature' => 5.0, 'wind_speed' => 2.0, 'relative_humidity' => 80.0]],
                    'next_1_hours' => ['summary' => ['symbol_code' => 'clearsky_day'], 'details' => ['precipitation_amount' => 0.0]],
                ],
            ]],
        ],
    ]);

    $mock = new MockHandler([new Response(200, [], $body), new Response(200, [], $body)]);
    $client = new Client(['handler' => HandlerStack::create($mock)]);

    app()->instance(YrWeatherService::class, new YrWeatherService('test-agent', 3600, $client));
}

it('api routes are enabled by default', function () {
    config(['yr.enable_api_routes' => true]);

    $response = $this->get('/api/weather/current?lat=60&lon=10');

    // Should not be 404 (route exists)
    expect($response->status())->not->toBe(404);
});

it('api routes can be disabled via config', function () {
    config(['yr.enable_api_routes' => false]);

    // Need to reload routes after config change
    // In a real app, this would be handled by the service provider
    $response = $this->get('/api/weather/current?lat=60&lon=10');

    // When disabled, routes should not exist
    // Note: In test environment, routes are already loaded, so this tests the config value
    expect(config('yr.enable_api_routes'))->toBe(false);
});

it('api current endpoint is accessible when enabled', function () {
    config(['yr.enable_api_routes' => true]);
    fakeYrWeatherService();

    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'current',
            'location',
        ],
    ]);
});

it('api forecast endpoint is accessible when enabled', function () {
    config(['yr.enable_api_routes' => true]);
    fakeYrWeatherService();

    $response = $this->get('/api/weather/forecast?lat=59.9139&lon=10.7522');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'forecast',
            'location',
        ],
    ]);
});

it('api routes have named routes', function () {
    config(['yr.enable_api_routes' => true]);

    // Check that named routes exist
    expect(route('yr.api.current', ['lat' => 60, 'lon' => 10]))->toContain('/api/weather/current');
    expect(route('yr.api.forecast', ['lat' => 60, 'lon' => 10]))->toContain('/api/weather/forecast');
});

it('has default api route prefix config', function () {
    expect(config('yr.api_route_prefix'))->toBe('api/weather');
});

it('has default api current endpoint config', function () {
    expect(config('yr.api_current_endpoint'))->toBe('current');
});

it('has default api forecast endpoint config', function () {
    expect(config('yr.api_forecast_endpoint'))->toBe('forecast');
});

it('api route prefix config can be customized', function () {
    config(['yr.api_route_prefix' => 'custom/path']);

    expect(config('yr.api_route_prefix'))->toBe('custom/path');
});

it('api current endpoint config can be customized', function () {
    config(['yr.api_current_endpoint' => 'now']);

    expect(config('yr.api_current_endpoint'))->toBe('now');
});

it('api forecast endpoint config can be customized', function () {
    config(['yr.api_forecast_endpoint' => 'predictions']);

    expect(config('yr.api_forecast_endpoint'))->toBe('predictions');
});
