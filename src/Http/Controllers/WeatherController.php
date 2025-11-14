<?php

namespace Ekstremedia\LaravelYr\Http\Controllers;

use Ekstremedia\LaravelYr\Services\GeocodingService;
use Ekstremedia\LaravelYr\Services\YrWeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * Weather API Controller
 *
 * IMPORTANT: Attribution Requirements
 * ====================================
 * All weather data served through this API comes from The Norwegian Meteorological
 * Institute (MET Norway) and is licensed under:
 * - Norwegian Licence for Open Government Data (NLOD) 2.0
 * - Creative Commons 4.0 BY International (CC BY 4.0)
 *
 * When displaying this data in your application, you MUST provide appropriate
 * attribution to MET Norway. Example:
 * "Weather data from The Norwegian Meteorological Institute (MET Norway)"
 *
 * For full licensing details, see:
 * https://api.met.no/doc/License
 */
class WeatherController extends Controller
{
    public function __construct(
        private YrWeatherService $weatherService,
        private GeocodingService $geocodingService
    ) {}

    /**
     * Get current weather for given location (coordinates or address)
     *
     * Example usage:
     * GET /api/weather/current?lat=59.9139&lon=10.7522&altitude=90
     * GET /api/weather/current?address=Oslo,Norway
     */
    public function current(Request $request): JsonResponse
    {
        $location = $this->resolveLocation($request);

        if (isset($location['error'])) {
            return response()->json($location, $location['status'] ?? 400);
        }

        $weather = $this->weatherService->getCurrentWeather(
            $location['latitude'],
            $location['longitude'],
            $location['altitude'] ?? null
        );

        if (! $weather) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch weather data',
                'message' => 'The weather service is currently unavailable or returned invalid data.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current' => $weather,
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'altitude' => $location['altitude'] ?? null,
                    'name' => $location['display_name'] ?? null,
                ],
            ],
        ]);
    }

    /**
     * Get full forecast for given location (coordinates or address)
     *
     * Example usage:
     * GET /api/weather/forecast?lat=59.9139&lon=10.7522&altitude=90
     * GET /api/weather/forecast?address=Bergen,Norway&complete=1
     */
    public function forecast(Request $request): JsonResponse
    {
        $location = $this->resolveLocation($request);

        if (isset($location['error'])) {
            return response()->json($location, $location['status'] ?? 400);
        }

        $complete = $request->boolean('complete', false);

        $forecast = $this->weatherService->getForecast(
            $location['latitude'],
            $location['longitude'],
            $location['altitude'] ?? null,
            $complete
        );

        if (! $forecast) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch forecast data',
                'message' => 'The weather service is currently unavailable or returned invalid data.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'forecast' => $forecast,
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'altitude' => $location['altitude'] ?? null,
                    'name' => $location['display_name'] ?? null,
                ],
            ],
        ]);
    }

    /**
     * Resolve location from request (either coordinates or address)
     */
    private function resolveLocation(Request $request): array
    {
        // Check if coordinates are provided
        if ($request->has('lat') && $request->has('lon')) {
            $validator = Validator::make($request->all(), [
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
                'altitude' => 'nullable|integer|between:-500,9000',
            ]);

            if ($validator->fails()) {
                return [
                    'error' => 'Invalid coordinates',
                    'message' => $validator->errors()->first(),
                    'status' => 400,
                ];
            }

            return [
                'latitude' => (float) $request->get('lat'),
                'longitude' => (float) $request->get('lon'),
                'altitude' => $request->has('altitude') ? (int) $request->get('altitude') : null,
            ];
        }

        // Check if address is provided
        if ($request->has('address')) {
            $validator = Validator::make($request->all(), [
                'address' => 'required|string|min:3|max:255',
                'altitude' => 'nullable|integer|between:-500,9000',
            ]);

            if ($validator->fails()) {
                return [
                    'error' => 'Invalid address',
                    'message' => $validator->errors()->first(),
                    'status' => 400,
                ];
            }

            $geocoded = $this->geocodingService->geocode($request->get('address'));

            if (! $geocoded) {
                return [
                    'error' => 'Address not found',
                    'message' => 'Could not geocode the provided address. Please check your input and try again.',
                    'status' => 404,
                ];
            }

            return [
                'latitude' => $geocoded['latitude'],
                'longitude' => $geocoded['longitude'],
                'altitude' => $request->has('altitude') ? (int) $request->get('altitude') : null,
                'display_name' => $geocoded['display_name'],
            ];
        }

        return [
            'error' => 'Missing location parameters',
            'message' => 'Please provide either coordinates (lat & lon) or an address.',
            'status' => 400,
        ];
    }
}
