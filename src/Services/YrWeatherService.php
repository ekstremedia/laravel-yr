<?php

namespace YourVendor\LaravelYr\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class YrWeatherService
{
    private Client $client;
    private string $userAgent;
    private int $cacheTtl;
    private const API_BASE_URL = 'https://api.met.no/weatherapi/locationforecast/2.0/compact';

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
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function getForecast(float $latitude, float $longitude): ?array
    {
        $cacheKey = "yr_weather_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude) {
            try {
                $response = $this->client->get('', [
                    'query' => [
                        'lat' => $latitude,
                        'lon' => $longitude,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                return $this->formatWeatherData($data);
            } catch (GuzzleException $e) {
                report($e);
                return null;
            }
        });
    }

    /**
     * Get current weather conditions
     *
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function getCurrentWeather(float $latitude, float $longitude): ?array
    {
        $forecast = $this->getForecast($latitude, $longitude);

        if (!$forecast || empty($forecast['timeseries'])) {
            return null;
        }

        return $forecast['timeseries'][0] ?? null;
    }

    /**
     * Format raw API data into a more usable structure
     *
     * @param array $data
     * @return array
     */
    private function formatWeatherData(array $data): array
    {
        if (!isset($data['properties']['timeseries'])) {
            return [];
        }

        $timeseries = [];
        foreach ($data['properties']['timeseries'] as $entry) {
            $instant = $entry['data']['instant']['details'];
            $next1h = $entry['data']['next_1_hours'] ?? null;

            $timeseries[] = [
                'time' => $entry['time'],
                'temperature' => $instant['air_temperature'] ?? null,
                'wind_speed' => $instant['wind_speed'] ?? null,
                'wind_direction' => $instant['wind_from_direction'] ?? null,
                'humidity' => $instant['relative_humidity'] ?? null,
                'pressure' => $instant['air_pressure_at_sea_level'] ?? null,
                'precipitation' => $next1h['details']['precipitation_amount'] ?? null,
                'symbol_code' => $next1h['summary']['symbol_code'] ?? ($entry['data']['next_6_hours']['summary']['symbol_code'] ?? null),
            ];
        }

        return [
            'timeseries' => $timeseries,
            'updated_at' => $data['properties']['meta']['updated_at'] ?? null,
        ];
    }

    /**
     * Get weather symbol URL from met.no
     *
     * @param string $symbolCode
     * @return string
     */
    public function getSymbolUrl(string $symbolCode): string
    {
        return "https://api.met.no/weatherapi/weathericon/2.0/?symbol={$symbolCode}&content_type=image/svg+xml";
    }
}
