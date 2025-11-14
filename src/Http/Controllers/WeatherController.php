<?php

namespace YourVendor\LaravelYr\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use YourVendor\LaravelYr\Services\YrWeatherService;

class WeatherController extends Controller
{
    public function __construct(
        private YrWeatherService $weatherService
    ) {}

    /**
     * Get current weather for given coordinates
     *
     * Example usage:
     * GET /api/weather/current?lat=59.9139&lon=10.7522
     */
    public function current(): JsonResponse
    {
        $latitude = request()->query('lat');
        $longitude = request()->query('lon');

        if (!$latitude || !$longitude) {
            return response()->json([
                'error' => 'Missing required parameters: lat and lon'
            ], 400);
        }

        $weather = $this->weatherService->getCurrentWeather(
            (float) $latitude,
            (float) $longitude
        );

        if (!$weather) {
            return response()->json([
                'error' => 'Unable to fetch weather data'
            ], 500);
        }

        return response()->json([
            'data' => $weather,
            'coordinates' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]
        ]);
    }

    /**
     * Get full forecast for given coordinates
     *
     * Example usage:
     * GET /api/weather/forecast?lat=59.9139&lon=10.7522
     */
    public function forecast(): JsonResponse
    {
        $latitude = request()->query('lat');
        $longitude = request()->query('lon');

        if (!$latitude || !$longitude) {
            return response()->json([
                'error' => 'Missing required parameters: lat and lon'
            ], 400);
        }

        $forecast = $this->weatherService->getForecast(
            (float) $latitude,
            (float) $longitude
        );

        if (!$forecast) {
            return response()->json([
                'error' => 'Unable to fetch forecast data'
            ], 500);
        }

        return response()->json([
            'data' => $forecast,
            'coordinates' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]
        ]);
    }
}
