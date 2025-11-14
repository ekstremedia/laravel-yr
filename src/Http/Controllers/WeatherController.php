<?php

namespace Ekstremedia\LaravelYr\Http\Controllers;

use Ekstremedia\LaravelYr\Services\GeocodingService;
use Ekstremedia\LaravelYr\Services\MoonService;
use Ekstremedia\LaravelYr\Services\SunService;
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
        private GeocodingService $geocodingService,
        private SunService $sunService,
        private MoonService $moonService
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
     * Get sunrise/sunset data for given location (coordinates or address)
     *
     * Example usage:
     * GET /api/weather/sun?lat=59.9139&lon=10.7522
     * GET /api/weather/sun?address=Oslo,Norway&date=2025-12-25&offset=1
     */
    public function sun(Request $request): JsonResponse
    {
        $location = $this->resolveLocation($request, includeAltitude: false);

        if (isset($location['error'])) {
            return response()->json($location, $location['status'] ?? 400);
        }

        $date = $request->get('date'); // Optional, defaults to today
        $offset = (int) $request->get('offset', 0); // Timezone offset in hours

        $sunData = $this->sunService->getSunData(
            $location['latitude'],
            $location['longitude'],
            $date,
            $offset
        );

        if (! $sunData) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch sun data',
                'message' => 'The sunrise service is currently unavailable or returned invalid data.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sun' => $sunData,
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'name' => $location['display_name'] ?? null,
                ],
            ],
        ]);
    }

    /**
     * Get moon phase and rise/set data for given location (coordinates or address)
     *
     * Example usage:
     * GET /api/weather/moon?lat=59.9139&lon=10.7522
     * GET /api/weather/moon?address=Oslo,Norway&date=2025-12-25&offset=1
     */
    public function moon(Request $request): JsonResponse
    {
        $location = $this->resolveLocation($request, includeAltitude: false);

        if (isset($location['error'])) {
            return response()->json($location, $location['status'] ?? 400);
        }

        $date = $request->get('date'); // Optional, defaults to today
        $offset = (int) $request->get('offset', 0); // Timezone offset in hours

        $moonData = $this->moonService->getMoonData(
            $location['latitude'],
            $location['longitude'],
            $date,
            $offset
        );

        if (! $moonData) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch moon data',
                'message' => 'The moon service is currently unavailable or returned invalid data.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'moon' => $moonData,
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'name' => $location['display_name'] ?? null,
                ],
            ],
        ]);
    }

    /**
     * Resolve location from request (either coordinates or address)
     */
    private function resolveLocation(Request $request, bool $includeAltitude = true): array
    {
        // Check if coordinates are provided
        if ($request->has('lat') && $request->has('lon')) {
            $rules = [
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
            ];

            if ($includeAltitude) {
                $rules['altitude'] = 'nullable|integer|between:-500,9000';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return [
                    'error' => 'Invalid coordinates',
                    'message' => $validator->errors()->first(),
                    'status' => 400,
                ];
            }

            $result = [
                'latitude' => (float) $request->get('lat'),
                'longitude' => (float) $request->get('lon'),
            ];

            if ($includeAltitude) {
                $result['altitude'] = $request->has('altitude') ? (int) $request->get('altitude') : null;
            }

            return $result;
        }

        // Check if address is provided
        if ($request->has('address')) {
            $rules = [
                'address' => 'required|string|min:3|max:255',
            ];

            if ($includeAltitude) {
                $rules['altitude'] = 'nullable|integer|between:-500,9000';
            }

            $validator = Validator::make($request->all(), $rules);

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

            $result = [
                'latitude' => $geocoded['latitude'],
                'longitude' => $geocoded['longitude'],
                'display_name' => $geocoded['display_name'],
            ];

            if ($includeAltitude) {
                $result['altitude'] = $request->has('altitude') ? (int) $request->get('altitude') : null;
            }

            return $result;
        }

        return [
            'error' => 'Missing location parameters',
            'message' => 'Please provide either coordinates (lat & lon) or an address.',
            'status' => 400,
        ];
    }
}
