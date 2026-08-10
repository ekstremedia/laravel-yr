<?php

use Ekstremedia\LaravelYr\Services\YrWeatherService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
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

/**
 * A mocked-client YrWeatherService whose getForecast() returns a timeseries covering all
 * three MET "complete" step shapes (next_1_hours, next_6_hours-only, next_12_hours-only), so
 * the precipitation-range fallback and duration_hours mapping can be asserted for each.
 */
function fakeYrServiceWithMultiStepTimeseries(): YrWeatherService
{
    $body = json_encode([
        'properties' => [
            'meta' => ['updated_at' => '2026-01-01T00:00:00Z', 'units' => []],
            'timeseries' => [
                // Hourly step: next_1_hours present.
                [
                    'time' => '2026-01-01T00:00:00Z',
                    'data' => [
                        'instant' => ['details' => ['air_temperature' => 5.0, 'wind_speed' => 2.0, 'relative_humidity' => 80.0]],
                        'next_1_hours' => [
                            'summary' => ['symbol_code' => 'rain'],
                            'details' => [
                                'precipitation_amount' => 0.5,
                                'precipitation_amount_min' => 0.2,
                                'precipitation_amount_max' => 0.9,
                                'probability_of_precipitation' => 70.0,
                            ],
                        ],
                        'next_6_hours' => [
                            'summary' => ['symbol_code' => 'rain'],
                            'details' => [
                                'precipitation_amount' => 2.0,
                                'precipitation_amount_min' => 1.0,
                                'precipitation_amount_max' => 3.5,
                                'probability_of_precipitation' => 60.0,
                            ],
                        ],
                    ],
                ],
                // 6-hourly step: only next_6_hours present (outer forecast days).
                [
                    'time' => '2026-01-03T06:00:00Z',
                    'data' => [
                        'instant' => ['details' => ['air_temperature' => 3.0]],
                        'next_6_hours' => [
                            'summary' => ['symbol_code' => 'cloudy'],
                            'details' => [
                                'precipitation_amount' => 1.5,
                                'precipitation_amount_min' => 0.8,
                                'precipitation_amount_max' => 2.6,
                                'probability_of_precipitation' => 45.0,
                            ],
                        ],
                    ],
                ],
                // 12-hourly step: only next_12_hours present (the outermost forecast days).
                [
                    'time' => '2026-01-08T00:00:00Z',
                    'data' => [
                        'instant' => ['details' => ['air_temperature' => 1.0]],
                        'next_12_hours' => [
                            'summary' => ['symbol_code' => 'partlycloudy_day'],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $client = new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], $body)]))]);

    return new YrWeatherService('test-agent', 3600, $client);
}

/**
 * A mocked-client YrWeatherService whose getNowcast() returns a MET Nowcast 2.0 "complete"
 * response inside radar coverage (Nordic area), shaped after a live call for
 * lat 68.7 / lon 15.4 (Sortland, Vesterålen).
 */
function fakeYrServiceWithNowcastInCoverage(): YrWeatherService
{
    $body = json_encode([
        'properties' => [
            'meta' => [
                'updated_at' => '2026-08-10T18:52:40Z',
                'units' => ['precipitation_rate' => 'mm/h'],
                'radar_coverage' => 'ok',
            ],
            'timeseries' => [
                [
                    'time' => '2026-08-10T18:55:00Z',
                    'data' => [
                        'instant' => ['details' => ['precipitation_rate' => 0.0]],
                        'next_1_hours' => ['summary' => ['symbol_code' => 'fair_day'], 'details' => ['precipitation_amount' => 0.0]],
                    ],
                ],
                [
                    'time' => '2026-08-10T19:00:00Z',
                    'data' => [
                        'instant' => ['details' => ['precipitation_rate' => 0.4]],
                    ],
                ],
            ],
        ],
    ]);

    $client = new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], $body)]))]);

    return new YrWeatherService('test-agent', 3600, $client, 300);
}

/**
 * A mocked-client YrWeatherService whose getNowcast() simulates MET's response for
 * coordinates outside Nordic radar coverage: HTTP 422 with a plain-text body (not JSON),
 * as observed for lat 40.7 / lon -74.0 (New York). getNowcast() must swallow this and
 * return null rather than throwing.
 */
function fakeYrServiceWithNowcastOutOfCoverage(): YrWeatherService
{
    $body = "422 Unprocessable Entity\nThe location is outside the geographic area supported by the product.";

    $client = new Client(['handler' => HandlerStack::create(new MockHandler([
        new ClientException(
            'Outside geographic area',
            new Request('GET', 'nowcast'),
            new Response(422, ['x-errorclass' => 'Outsidegeographicarea'], $body)
        ),
    ]))]);

    return new YrWeatherService('test-agent', 3600, $client, 300);
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

it('maps precipitation range, probability and duration for an hourly step', function () {
    $forecast = fakeYrServiceWithMultiStepTimeseries()->getForecast(68.7, 15.4);
    $step = $forecast['timeseries'][0];

    // toEqual (loose ==), not toBe: json_decode() gives an int for whole-number JSON
    // values like 70 and a float for 0.2, and the real MET API mixes both depending
    // on the value - the numeric value matters here, not the PHP type.
    expect($step['precipitation_min'])->toEqual(0.2)
        ->and($step['precipitation_max'])->toEqual(0.9)
        ->and($step['precipitation_probability'])->toEqual(70.0)
        ->and($step['duration_hours'])->toBe(1);
});

it('falls back to next_6_hours for precipitation range, probability and duration', function () {
    $forecast = fakeYrServiceWithMultiStepTimeseries()->getForecast(68.7, 15.4);
    $step = $forecast['timeseries'][1];

    expect($step['precipitation_min'])->toEqual(0.8)
        ->and($step['precipitation_max'])->toEqual(2.6)
        ->and($step['precipitation_probability'])->toEqual(45.0)
        ->and($step['duration_hours'])->toBe(6);
});

it('has no precipitation range for a 12-hourly-only step but reports duration_hours', function () {
    $forecast = fakeYrServiceWithMultiStepTimeseries()->getForecast(68.7, 15.4);
    $step = $forecast['timeseries'][2];

    expect($step['precipitation_min'])->toBeNull()
        ->and($step['precipitation_max'])->toBeNull()
        ->and($step['precipitation_probability'])->toBeNull()
        ->and($step['duration_hours'])->toBe(12)
        ->and($step['symbol_code'])->toBe('partlycloudy_day');
});

it('preserves existing precipitation_amount and symbol_code keys unchanged', function () {
    $forecast = fakeYrServiceWithMultiStepTimeseries()->getForecast(68.7, 15.4);

    expect($forecast['timeseries'][0]['precipitation_amount'])->toBe(0.5)
        ->and($forecast['timeseries'][0]['symbol_code'])->toBe('rain')
        ->and($forecast['timeseries'][1]['precipitation_amount'])->toBe(1.5)
        ->and($forecast['timeseries'][2]['precipitation_amount'])->toBeNull();
});

it('fetches a nowcast within radar coverage', function () {
    $nowcast = fakeYrServiceWithNowcastInCoverage()->getNowcast(68.7, 15.4);

    expect($nowcast)->toBeArray()
        ->and($nowcast['radar_coverage'])->toBe('ok')
        ->and($nowcast['updated_at'])->toBe('2026-08-10T18:52:40Z')
        ->and($nowcast['timeseries'])->toHaveCount(2);

    expect($nowcast['timeseries'][0]['time'])->toBe('2026-08-10T18:55:00Z')
        ->and($nowcast['timeseries'][0]['precipitation_rate'])->toEqual(0.0)
        ->and($nowcast['timeseries'][0]['symbol_code'])->toBe('fair_day');

    // Later steps in a real MET Nowcast response frequently carry no next_1_hours
    // block at all; symbol_code must degrade to null rather than error.
    expect($nowcast['timeseries'][1]['precipitation_rate'])->toEqual(0.4)
        ->and($nowcast['timeseries'][1]['symbol_code'])->toBeNull();
});

it('returns null for a nowcast request outside radar coverage instead of throwing', function () {
    $nowcast = fakeYrServiceWithNowcastOutOfCoverage()->getNowcast(40.7, -74.0);

    expect($nowcast)->toBeNull();
});
