<?php

namespace Ekstremedia\LaravelYr\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class YrWeatherService
{
    private ClientInterface $client;

    private string $userAgent;

    private int $cacheTtl;

    private int $nowcastCacheTtl;

    private const API_BASE_URL = 'https://api.met.no/weatherapi/locationforecast/2.0/';

    private const NOWCAST_API_URL = 'https://api.met.no/weatherapi/nowcast/2.0/complete';

    /**
     * How long the last good answer is kept for revalidation after it has expired: long enough
     * to cover any sensible polling interval, short enough that an abandoned location does not
     * stay in the cache for good.
     */
    private const STALE_TTL = 86400;

    /**
     * @param  ClientInterface|null  $client  Inject a client (e.g. Guzzle MockHandler-backed) for
     *                                        testing without hitting the real MET API. Defaults to
     *                                        a real Guzzle client for normal application use.
     * @param  int  $nowcastCacheTtl  Cache TTL in seconds for getNowcast(). MET Nowcast 2.0 updates
     *                                every few minutes, so this is intentionally much shorter than
     *                                the locationforecast $cacheTtl.
     */
    public function __construct(string $userAgent, int $cacheTtl = 3600, ?ClientInterface $client = null, int $nowcastCacheTtl = 300)
    {
        $this->userAgent = $userAgent;
        $this->cacheTtl = $cacheTtl;
        $this->nowcastCacheTtl = $nowcastCacheTtl;
        $this->client = $client ?? new Client([
            'base_uri' => self::API_BASE_URL,
            'headers' => [
                'User-Agent' => $this->userAgent,
            ],
        ]);
    }

    /**
     * Get weather forecast for given coordinates
     *
     * The answer is cached until the Expires time MET sends with it (or `cache_ttl` when MET
     * sends none), and revalidated with If-Modified-Since once it has expired; see
     * cachedRequest().
     *
     * @param  int|null  $altitude  Altitude in meters (optional but recommended)
     * @param  bool  $complete  Use complete endpoint with all data (default: false for compact)
     */
    public function getForecast(float $latitude, float $longitude, ?int $altitude = null, bool $complete = false): ?array
    {
        // Truncate coordinates to max 4 decimals per MET API TOS
        $latitude = round($latitude, 4);
        $longitude = round($longitude, 4);

        $cacheKey = "yr_weather_{$latitude}_{$longitude}_".($altitude ?? 'auto').'_'.($complete ? 'complete' : 'compact');

        $query = [
            'lat' => $latitude,
            'lon' => $longitude,
        ];

        if ($altitude !== null) {
            $query['altitude'] = $altitude;
        }

        try {
            return $this->cachedRequest(
                $cacheKey,
                $complete ? 'complete' : 'compact',
                $query,
                $this->cacheTtl,
                fn (array $data): array => $this->formatWeatherData($data),
            );
        } catch (GuzzleException $e) {
            report($e);

            return null;
        }
    }

    /**
     * Get current weather conditions
     */
    public function getCurrentWeather(float $latitude, float $longitude, ?int $altitude = null): ?array
    {
        $forecast = $this->getForecast($latitude, $longitude, $altitude);

        if (! $forecast || empty($forecast['timeseries'])) {
            return null;
        }

        return $forecast['timeseries'][0] ?? null;
    }

    /**
     * Get a MET Nowcast 2.0 short-range precipitation forecast, used for the "next couple of
     * hours" radar-style nowcast box. Coverage is the Nordic countries only (Norway, Sweden,
     * Finland, Denmark); MET responds with a 422 "Outsidegeographicarea" error outside that
     * area. This never throws - any HTTP/decode failure, and any out-of-coverage response,
     * results in null so a caller can fall back to the regular forecast without a try/catch.
     * Cached like getForecast(), with `nowcast_cache_ttl` as the fallback when MET sends no
     * Expires.
     */
    public function getNowcast(float $latitude, float $longitude): ?array
    {
        // Truncate coordinates to max 4 decimals per MET API TOS
        $latitude = round($latitude, 4);
        $longitude = round($longitude, 4);

        try {
            // Absolute URL: overrides the client's locationforecast base_uri while still
            // reusing its User-Agent header and any test-injected handler/mock.
            return $this->cachedRequest(
                "yr_nowcast_{$latitude}_{$longitude}",
                self::NOWCAST_API_URL,
                ['lat' => $latitude, 'lon' => $longitude],
                $this->nowcastCacheTtl,
                fn (array $data): array => $this->formatNowcastData($data),
            );
        } catch (\Throwable $e) {
            // Includes GuzzleException (e.g. the 422 "Outsidegeographicarea" MET returns
            // for coordinates outside Nordic radar coverage) and JSON decode failures.
            report($e);

            return null;
        }
    }

    /**
     * Fetch a MET endpoint through the cache, the way MET's terms of service ask for: serve
     * the answer until its Expires time, then send If-Modified-Since and reuse the body on a
     * 304 Not Modified.
     *
     * Two cache entries per request. The fresh entry holds the formatted answer until Expires
     * (or $fallbackTtl when MET sends no Expires header); while it exists MET is not asked at
     * all. The stale entry holds the same answer and its Last-Modified for a day, outliving the
     * fresh one, so an expired answer can be revalidated and a 304 answered from it. A failed
     * request is never cached, so the next call tries again.
     *
     * (Until 1.2.0 this was a Cache::remember() whose own put after the callback replaced the
     * Expires TTL with `cache_ttl`, and a 304 was answered from the entry that had just missed,
     * which returned null.)
     *
     * @param  array<string, mixed>  $query
     * @param  callable(array<string, mixed>): array<string, mixed>  $format
     *
     * @throws GuzzleException|\JsonException
     */
    private function cachedRequest(string $cacheKey, string $uri, array $query, int $fallbackTtl, callable $format): ?array
    {
        $fresh = Cache::get($cacheKey);

        if (is_array($fresh)) {
            return $fresh;
        }

        $staleKey = "{$cacheKey}_stale";
        $stale = Cache::get($staleKey);
        $hasStale = is_array($stale) && array_key_exists('data', $stale);

        $options = ['query' => $query];

        if ($hasStale && ! empty($stale['last_modified'])) {
            $options['headers'] = ['If-Modified-Since' => $stale['last_modified']];
        }

        $response = $this->client->get($uri, $options);

        if ($response->getStatusCode() === 304) {
            if (! $hasStale) {
                // MET only answers 304 to a conditional request, which is only sent with a stale
                // copy; without one there is nothing to serve.
                return null;
            }

            $data = $stale['data'];
            $lastModified = $response->getHeader('Last-Modified')[0] ?? $stale['last_modified'] ?? null;
        } else {
            $data = $format(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
            $lastModified = $response->getHeader('Last-Modified')[0] ?? null;
        }

        Cache::put($cacheKey, $data, $this->ttlUntilExpires($response->getHeader('Expires')[0] ?? null, $fallbackTtl));
        Cache::put($staleKey, ['data' => $data, 'last_modified' => $lastModified], self::STALE_TTL);

        return $data;
    }

    /**
     * Seconds until MET's Expires time, at least 60 so a past or clock-skewed Expires does not
     * send a request on every call; the fallback when the header is missing or unreadable.
     */
    private function ttlUntilExpires(?string $expiresHeader, int $fallbackTtl): int
    {
        if ($expiresHeader === null || $expiresHeader === '') {
            return $fallbackTtl;
        }

        try {
            $expiresAt = Carbon::parse($expiresHeader);
        } catch (\Throwable) {
            return $fallbackTtl;
        }

        // Carbon 3's diffInSeconds() is signed: positive while Expires is still ahead.
        return max(60, (int) now()->diffInSeconds($expiresAt));
    }

    /**
     * Format raw MET Nowcast 2.0 API data into a flat structure
     */
    private function formatNowcastData(array $data): array
    {
        if (! isset($data['properties']['timeseries'])) {
            return ['radar_coverage' => 'unknown', 'updated_at' => null, 'timeseries' => []];
        }

        $meta = $data['properties']['meta'] ?? [];

        $timeseries = [];
        foreach ($data['properties']['timeseries'] as $entry) {
            $instant = $entry['data']['instant']['details'] ?? [];
            $next1h = $entry['data']['next_1_hours'] ?? null;

            $timeseries[] = [
                'time' => $entry['time'],
                'precipitation_rate' => $instant['precipitation_rate'] ?? null,
                'symbol_code' => $next1h['summary']['symbol_code'] ?? null,
            ];
        }

        return [
            'radar_coverage' => $meta['radar_coverage'] ?? 'unknown',
            'updated_at' => $meta['updated_at'] ?? null,
            'timeseries' => $timeseries,
        ];
    }

    /**
     * Format raw API data into a more usable structure
     */
    private function formatWeatherData(array $data): array
    {
        if (! isset($data['properties']['timeseries'])) {
            return [];
        }

        $timeseries = [];
        foreach ($data['properties']['timeseries'] as $entry) {
            $instant = $entry['data']['instant']['details'];
            $next1h = $entry['data']['next_1_hours'] ?? null;
            $next6h = $entry['data']['next_6_hours'] ?? null;
            $next12h = $entry['data']['next_12_hours'] ?? null;

            $timeseries[] = [
                'time' => $entry['time'],

                // Temperature data
                'temperature' => $instant['air_temperature'] ?? null,
                'feels_like' => $this->calculateFeelsLike(
                    $instant['air_temperature'] ?? null,
                    $instant['wind_speed'] ?? null,
                    $instant['relative_humidity'] ?? null
                ),

                // Wind data
                'wind_speed' => $instant['wind_speed'] ?? null,
                'wind_direction' => $instant['wind_from_direction'] ?? null,
                'wind_gust' => $instant['wind_speed_of_gust'] ?? null,

                // Atmospheric conditions
                'humidity' => $instant['relative_humidity'] ?? null,
                'pressure' => $instant['air_pressure_at_sea_level'] ?? null,
                'cloud_coverage' => $instant['cloud_area_fraction'] ?? null,
                'fog' => $instant['fog_area_fraction'] ?? null,
                'dew_point' => $instant['dew_point_temperature'] ?? null,

                // Precipitation
                'precipitation_amount' => $next1h['details']['precipitation_amount'] ?? $next6h['details']['precipitation_amount'] ?? null,
                'precipitation_min' => $next1h['details']['precipitation_amount_min']
                    ?? $next6h['details']['precipitation_amount_min']
                    ?? null,
                'precipitation_max' => $next1h['details']['precipitation_amount_max']
                    ?? $next6h['details']['precipitation_amount_max']
                    ?? null,
                'precipitation_probability' => $next1h['details']['probability_of_precipitation']
                    ?? $next6h['details']['probability_of_precipitation']
                    ?? null,

                // UV index
                'uv_index' => $instant['ultraviolet_index_clear_sky'] ?? null,

                // Symbol/weather condition
                'symbol_code' => $next1h['summary']['symbol_code']
                    ?? $next6h['summary']['symbol_code']
                    ?? $next12h['summary']['symbol_code']
                    ?? null,

                // Span of this step, in hours, so consumers don't need to re-derive it
                // from which next_*_hours block was present.
                'duration_hours' => match (true) {
                    $next1h !== null => 1,
                    $next6h !== null => 6,
                    $next12h !== null => 12,
                    default => null,
                },
            ];
        }

        return [
            'timeseries' => $timeseries,
            'updated_at' => $data['properties']['meta']['updated_at'] ?? null,
            'units' => $data['properties']['meta']['units'] ?? null,
        ];
    }

    /**
     * Calculate feels-like temperature (wind chill or heat index)
     */
    private function calculateFeelsLike(?float $temp, ?float $windSpeed, ?float $humidity): ?float
    {
        if ($temp === null) {
            return null;
        }

        // Wind chill for cold temperatures
        if ($temp <= 10 && $windSpeed !== null && $windSpeed > 1.34) {
            $windKmh = $windSpeed * 3.6;
            $feelsLike = 13.12 + 0.6215 * $temp - 11.37 * pow($windKmh, 0.16) + 0.3965 * $temp * pow($windKmh, 0.16);

            return round($feelsLike, 1);
        }

        // Heat index for warm temperatures
        if ($temp >= 27 && $humidity !== null) {
            $hi = -8.78469475556
                + 1.61139411 * $temp
                + 2.33854883889 * $humidity
                - 0.14611605 * $temp * $humidity
                - 0.012308094 * pow($temp, 2)
                - 0.0164248277778 * pow($humidity, 2)
                + 0.002211732 * pow($temp, 2) * $humidity
                + 0.00072546 * $temp * pow($humidity, 2)
                - 0.000003582 * pow($temp, 2) * pow($humidity, 2);

            return round($hi, 1);
        }

        return $temp;
    }

    /**
     * Get weather symbol URL (local SVG or fallback to met.no)
     */
    public function getSymbolUrl(string $symbolCode): string
    {
        // Check if local symbol exists in published public directory
        $publicPath = public_path("vendor/laravel-yr/symbols/{$symbolCode}.svg");
        if (file_exists($publicPath)) {
            return asset("vendor/laravel-yr/symbols/{$symbolCode}.svg");
        }

        // Check if symbol exists in package resources (for development)
        $packagePath = __DIR__."/resources/symbols/{$symbolCode}.svg";
        if (file_exists($packagePath)) {
            // Return data URI for package symbols when not published
            $svg = file_get_contents($packagePath);

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        }

        // Fallback to MET.no API
        return "https://api.met.no/weatherapi/weathericon/2.0/?symbol={$symbolCode}&content_type=image/svg+xml";
    }
}
