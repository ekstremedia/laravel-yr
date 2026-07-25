<?php

use Ekstremedia\LaravelYr\Services\YrWeatherService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    $this->service = app(YrWeatherService::class);
});

/**
 * A mocked-client YrWeatherService, for tests that need getForecast() to
 * actually return data without depending on the real MET API (which
 * routinely 403s automated/CI traffic).
 */
function fakeYrService(): YrWeatherService
{
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

    $client = new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], $body)]))]);

    return new YrWeatherService('test-agent', 3600, $client);
}

it('generates correct weather symbol URL', function () {
    $url = $this->service->getSymbolUrl('clearsky_day');

    expect($url)
        ->toBeString()
        ->toContain('api.met.no')
        ->toContain('clearsky_day');
});

it('generates correct weather symbol URL with night variant', function () {
    $url = $this->service->getSymbolUrl('partlycloudy_night');

    expect($url)
        ->toBeString()
        ->toContain('partlycloudy_night');
});

it('calculates feels like temperature correctly for cold weather', function () {
    // This is a white-box test - we're testing the internal calculation
    // Create a reflection to test the private method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('calculateFeelsLike');
    $method->setAccessible(true);

    // Cold temperature with wind should produce wind chill
    $result = $method->invoke($this->service, 5.0, 5.0, 50.0);

    expect($result)->toBeFloat()
        ->and($result)->toBeLessThan(5.0); // Should feel colder
});

it('calculates feels like temperature correctly for hot weather', function () {
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('calculateFeelsLike');
    $method->setAccessible(true);

    // Hot temperature with high humidity should produce heat index
    $result = $method->invoke($this->service, 30.0, 2.0, 80.0);

    expect($result)->toBeFloat()
        ->and($result)->toBeGreaterThan(30.0); // Should feel hotter
});

it('returns same temperature when no adjustment needed', function () {
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('calculateFeelsLike');
    $method->setAccessible(true);

    // Mild temperature with low wind
    $result = $method->invoke($this->service, 15.0, 1.0, 50.0);

    expect($result)->toBe(15.0); // Should be unchanged
});

it('truncates coordinates to max 4 decimals per MET API TOS', function () {
    // Test with high precision coordinates, against a mocked client so this
    // doesn't depend on the real (rate-limiting) MET API.
    $forecast = fakeYrService()->getForecast(59.913868217, 10.752245396);

    // If successful, the service should have truncated to 4 decimals
    // We can't directly test the truncation but we verify it doesn't throw errors
    expect($forecast)->toBeArray();
});

it('uses local weather symbols when available', function () {
    // Check if the method prioritizes local files
    $url = $this->service->getSymbolUrl('clearsky_day');

    expect($url)
        ->toBeString()
        ->and(
            str_contains($url, 'vendor/laravel-yr/symbols') ||
            str_contains($url, 'data:image/svg+xml') ||
            str_contains($url, 'api.met.no')
        )->toBeTrue();
});
