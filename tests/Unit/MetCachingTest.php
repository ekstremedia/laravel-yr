<?php

/*
 * How the services cache MET's answers: until the Expires time MET sends, then a conditional
 * request (If-Modified-Since) whose 304 reuses the last body. MET's terms of service ask for
 * exactly this, and before 1.2.1 the Expires TTL was overwritten by `cache_ttl` and a 304
 * returned null.
 */

use Ekstremedia\LaravelYr\Services\MoonService;
use Ekstremedia\LaravelYr\Services\SunService;
use Ekstremedia\LaravelYr\Services\YrWeatherService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;

function forecastBody(float $temperature): string
{
    return json_encode([
        'properties' => [
            'meta' => ['updated_at' => '2026-10-19T10:00:00Z', 'units' => []],
            'timeseries' => [[
                'time' => '2026-10-19T10:00:00Z',
                'data' => [
                    'instant' => ['details' => ['air_temperature' => $temperature]],
                    'next_1_hours' => ['summary' => ['symbol_code' => 'cloudy'], 'details' => ['precipitation_amount' => 0.0]],
                ],
            ]],
        ],
    ]);
}

/**
 * A YrWeatherService on a queue of mocked responses, with the requests it sends recorded in
 * $history (an ArrayAccess the caller passes by reference).
 *
 * @param  list<Response|Throwable>  $responses
 */
function serviceAnswering(array $responses, array &$history, int $cacheTtl = 3600, int $nowcastCacheTtl = 300): YrWeatherService
{
    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($history));

    return new YrWeatherService('test-agent', $cacheTtl, new Client(['handler' => $stack]), $nowcastCacheTtl);
}

function httpDate(DateTimeInterface $time): string
{
    return gmdate('D, d M Y H:i:s \G\M\T', $time->getTimestamp());
}

beforeEach(function () {
    $this->freezeTime();
});

it('serves a forecast from the cache until MET\'s Expires time, longer than cache_ttl', function () {
    $history = [];
    $service = serviceAnswering([
        new Response(200, ['Expires' => httpDate(now()->addMinutes(30))], forecastBody(5.0)),
        new Response(200, ['Expires' => httpDate(now()->addHours(2))], forecastBody(6.0)),
    ], $history, cacheTtl: 60);

    expect($service->getForecast(59.2676, 10.4076)['timeseries'][0]['temperature'])->toEqual(5.0);

    $this->travel(20)->minutes();

    expect($service->getForecast(59.2676, 10.4076)['timeseries'][0]['temperature'])->toEqual(5.0)
        ->and($history)->toHaveCount(1);
});

it('asks MET again once Expires has passed', function () {
    $history = [];
    $service = serviceAnswering([
        new Response(200, ['Expires' => httpDate(now()->addMinutes(30))], forecastBody(5.0)),
        new Response(200, ['Expires' => httpDate(now()->addHours(2))], forecastBody(6.0)),
    ], $history);

    $service->getForecast(59.2676, 10.4076);
    $this->travel(31)->minutes();

    expect($service->getForecast(59.2676, 10.4076)['timeseries'][0]['temperature'])->toEqual(6.0)
        ->and($history)->toHaveCount(2);
});

it('revalidates an expired forecast with If-Modified-Since and reuses the body on a 304', function () {
    $history = [];
    $lastModified = 'Sun, 19 Oct 2026 09:58:00 GMT';
    $service = serviceAnswering([
        new Response(200, ['Expires' => httpDate(now()->addMinutes(30)), 'Last-Modified' => $lastModified], forecastBody(5.0)),
        new Response(304, ['Expires' => httpDate(now()->addMinutes(90))]),
    ], $history);

    $service->getForecast(59.2676, 10.4076);
    $this->travel(31)->minutes();
    $forecast = $service->getForecast(59.2676, 10.4076);

    expect($forecast['timeseries'][0]['temperature'])->toEqual(5.0)
        ->and($history[1]['request']->getHeaderLine('If-Modified-Since'))->toBe($lastModified);
});

it('keeps a revalidated forecast until the Expires time of the 304', function () {
    $history = [];
    $service = serviceAnswering([
        new Response(200, ['Expires' => httpDate(now()->addMinutes(30)), 'Last-Modified' => 'Sun, 19 Oct 2026 09:58:00 GMT'], forecastBody(5.0)),
        new Response(304, ['Expires' => httpDate(now()->addMinutes(90))]),
    ], $history);

    $service->getForecast(59.2676, 10.4076);
    $this->travel(31)->minutes();
    $service->getForecast(59.2676, 10.4076);
    $this->travel(30)->minutes();

    expect($service->getForecast(59.2676, 10.4076)['timeseries'][0]['temperature'])->toEqual(5.0)
        ->and($history)->toHaveCount(2);
});

it('sends no If-Modified-Since on the first request', function () {
    $history = [];
    $service = serviceAnswering([new Response(200, [], forecastBody(5.0))], $history);

    $service->getForecast(59.2676, 10.4076);

    expect($history[0]['request']->hasHeader('If-Modified-Since'))->toBeFalse();
});

it('falls back to cache_ttl when MET sends no Expires header', function () {
    $history = [];
    $service = serviceAnswering([
        new Response(200, [], forecastBody(5.0)),
        new Response(200, [], forecastBody(6.0)),
    ], $history, cacheTtl: 600);

    $service->getForecast(59.2676, 10.4076);
    $this->travel(9)->minutes();
    $service->getForecast(59.2676, 10.4076);

    expect($history)->toHaveCount(1);

    $this->travel(2)->minutes();

    expect($service->getForecast(59.2676, 10.4076)['timeseries'][0]['temperature'])->toEqual(6.0);
});

it('caches an Expires in the past for 60 seconds, not zero', function () {
    $history = [];
    $service = serviceAnswering([
        new Response(200, ['Expires' => httpDate(now()->subMinutes(5))], forecastBody(5.0)),
        new Response(200, [], forecastBody(6.0)),
    ], $history);

    $service->getForecast(59.2676, 10.4076);
    $this->travel(59)->seconds();
    $service->getForecast(59.2676, 10.4076);

    expect($history)->toHaveCount(1);
});

it('never caches a failed request', function () {
    $history = [];
    $service = serviceAnswering([
        new Response(500, [], 'MET is down'),
        new Response(200, ['Expires' => httpDate(now()->addMinutes(30))], forecastBody(5.0)),
    ], $history);

    expect($service->getForecast(59.2676, 10.4076))->toBeNull()
        ->and($service->getForecast(59.2676, 10.4076)['timeseries'][0]['temperature'])->toEqual(5.0);
});

it('returns null on a 304 when there is no earlier answer to reuse', function () {
    $history = [];
    $service = serviceAnswering([new Response(304)], $history);

    expect($service->getForecast(59.2676, 10.4076))->toBeNull();
});

it('serves a nowcast from the cache until its Expires time', function () {
    $history = [];
    $nowcast = json_encode(['properties' => [
        'meta' => ['updated_at' => '2026-10-19T10:00:00Z', 'radar_coverage' => 'ok'],
        'timeseries' => [['time' => '2026-10-19T10:05:00Z', 'data' => ['instant' => ['details' => ['precipitation_rate' => 0.0]]]]],
    ]]);
    $service = serviceAnswering([
        new Response(200, ['Expires' => httpDate(now()->addMinutes(10))], $nowcast),
        new Response(200, [], $nowcast),
    ], $history, nowcastCacheTtl: 60);

    $service->getNowcast(59.2676, 10.4076);
    $this->travel(5)->minutes();
    $service->getNowcast(59.2676, 10.4076);

    expect($history)->toHaveCount(1);
});

it('caches sun data until MET\'s Expires time and stores the formatted answer', function () {
    config()->set('yr.cache_ttl', 60);
    Http::fake([
        'api.met.no/*' => Http::response(
            ['properties' => ['sunrise' => ['time' => '2026-10-19T08:05+02:00'], 'sunset' => ['time' => '2026-10-19T18:00+02:00']]],
            200,
            ['Expires' => httpDate(now()->addHours(6))],
        ),
    ]);
    $service = new SunService();

    $first = $service->getSunData(59.2676, 10.4076, '2026-10-19');
    $this->travel(2)->hours();
    $second = $service->getSunData(59.2676, 10.4076, '2026-10-19');

    expect($second)->toBe($first)
        ->and($second['sunrise']['time'])->toBe('2026-10-19T08:05+02:00');
    Http::assertSentCount(1);
});

it('caches moon data until MET\'s Expires time', function () {
    config()->set('yr.cache_ttl', 60);
    Http::fake([
        'api.met.no/*' => Http::response(
            ['properties' => ['moonrise' => ['time' => '2026-10-19T15:00+02:00'], 'moonphase' => 90.0]],
            200,
            ['Expires' => httpDate(now()->addHours(6))],
        ),
    ]);
    $service = new MoonService();

    $service->getMoonData(59.2676, 10.4076, '2026-10-19');
    $this->travel(2)->hours();
    $service->getMoonData(59.2676, 10.4076, '2026-10-19');

    Http::assertSentCount(1);
});

it('never caches a failed sun request', function () {
    Http::fake([
        'api.met.no/*' => Http::sequence()
            ->push('MET is down', 500)
            ->push(['properties' => ['sunrise' => ['time' => '2026-10-19T08:05+02:00']]], 200),
    ]);
    $service = new SunService();

    expect($service->getSunData(59.2676, 10.4076, '2026-10-19'))->toBeNull()
        ->and($service->getSunData(59.2676, 10.4076, '2026-10-19')['sunrise']['time'])->toBe('2026-10-19T08:05+02:00');
});
