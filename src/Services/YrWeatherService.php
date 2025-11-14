<?php

namespace YourVendor\LaravelYr\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class YrWeatherService
{
    private Client $client;

    private string $userAgent;

    private int $cacheTtl;

    private const API_BASE_URL = 'https://api.met.no/weatherapi/locationforecast/2.0/';

    public function __construct(string $userAgent, int $cacheTtl = 3600)
    {
        $this->userAgent = $userAgent;
        $this->cacheTtl = $cacheTtl;
        $this->client = new Client([
            'base_uri' => self::API_BASE_URL,
            'headers' => [
                'User-Agent' => $this->userAgent,
            ],
        ]);
    }

    /**
     * Get weather forecast for given coordinates
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
        $cacheMetaKey = "{$cacheKey}_meta";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude, $altitude, $complete, $cacheKey, $cacheMetaKey) {
            try {
                $query = [
                    'lat' => $latitude,
                    'lon' => $longitude,
                ];

                if ($altitude !== null) {
                    $query['altitude'] = $altitude;
                }

                $endpoint = $complete ? 'complete' : 'compact';

                // Add If-Modified-Since header if we have cached metadata
                $requestOptions = ['query' => $query];
                $cachedMeta = Cache::get($cacheMetaKey);
                if ($cachedMeta && isset($cachedMeta['last_modified'])) {
                    $requestOptions['headers'] = [
                        'If-Modified-Since' => $cachedMeta['last_modified'],
                    ];
                }

                $response = $this->client->get($endpoint, $requestOptions);

                // If 304 Not Modified, return cached data
                if ($response->getStatusCode() === 304) {
                    return Cache::get($cacheKey);
                }

                $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                // Store Last-Modified header for future If-Modified-Since requests
                $lastModified = $response->getHeader('Last-Modified')[0] ?? null;
                if ($lastModified) {
                    Cache::put($cacheMetaKey, ['last_modified' => $lastModified], $this->cacheTtl);
                }

                // Get cache TTL from Expires header if available
                $expiresHeader = $response->getHeader('Expires')[0] ?? null;
                if ($expiresHeader) {
                    $expiresAt = Carbon::parse($expiresHeader);
                    $ttl = max(60, $expiresAt->diffInSeconds(now())); // Minimum 60 seconds
                    Cache::put($cacheKey, $this->formatWeatherData($data), $ttl);
                }

                return $this->formatWeatherData($data);
            } catch (GuzzleException $e) {
                report($e);

                return;
            }
        });
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
                'precipitation_min' => $next1h['details']['precipitation_amount_min'] ?? null,
                'precipitation_max' => $next1h['details']['precipitation_amount_max'] ?? null,

                // UV index
                'uv_index' => $instant['ultraviolet_index_clear_sky'] ?? null,

                // Symbol/weather condition
                'symbol_code' => $next1h['summary']['symbol_code']
                    ?? $next6h['summary']['symbol_code']
                    ?? $next12h['summary']['symbol_code']
                    ?? null,
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
