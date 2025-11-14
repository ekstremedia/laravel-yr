<?php

namespace Ekstremedia\LaravelYr\Services;

/**
 * Weather Helper Service
 *
 * Provides convenient methods for developers to fetch weather data
 * without going through HTTP endpoints. This is the recommended way
 * to use the package in your Laravel application code.
 *
 * Example usage:
 * ```php
 * use Ekstremedia\LaravelYr\Services\WeatherHelper;
 *
 * $helper = app(WeatherHelper::class);
 *
 * // Get weather by address
 * $result = $helper->getWeatherByAddress('Oslo, Norway');
 *
 * // Get weather by coordinates
 * $result = $helper->getWeatherByCoordinates(59.9139, 10.7522, altitude: 90);
 *
 * // Get forecast
 * $forecast = $helper->getForecastByAddress('Bergen, Norway', days: 5);
 * ```
 *
 * IMPORTANT: Attribution Requirements
 * ====================================
 * All weather data comes from The Norwegian Meteorological Institute (MET Norway)
 * and is licensed under NLOD 2.0 and CC BY 4.0.
 *
 * When displaying this data, you MUST provide appropriate attribution:
 * "Weather data from The Norwegian Meteorological Institute (MET Norway)"
 *
 * For full licensing details, see: https://api.met.no/doc/License
 */
class WeatherHelper
{
    public function __construct(
        private YrWeatherService $weatherService,
        private GeocodingService $geocodingService,
        private SunService $sunService,
        private MoonService $moonService
    ) {}

    /**
     * Get current weather by address
     *
     * @param  string  $address  Full address (e.g., "Oslo, Norway")
     * @param  int|null  $altitude  Altitude in meters (optional, -500 to 9000)
     * @return array|null Returns weather data array or null on failure
     *
     * @throws \InvalidArgumentException If address is invalid
     */
    public function getWeatherByAddress(string $address, ?int $altitude = null): ?array
    {
        $this->validateAddress($address);
        $this->validateAltitude($altitude);

        $geocoded = $this->geocodingService->geocode($address);

        if (! $geocoded) {
            return null;
        }

        $weather = $this->weatherService->getCurrentWeather(
            $geocoded['latitude'],
            $geocoded['longitude'],
            $altitude
        );

        if (! $weather) {
            return null;
        }

        return [
            'current' => $weather,
            'location' => [
                'latitude' => $geocoded['latitude'],
                'longitude' => $geocoded['longitude'],
                'altitude' => $altitude,
                'name' => $geocoded['display_name'],
            ],
        ];
    }

    /**
     * Get current weather by coordinates
     *
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @param  int|null  $altitude  Altitude in meters (optional, -500 to 9000)
     * @return array|null Returns weather data array or null on failure
     *
     * @throws \InvalidArgumentException If coordinates are invalid
     */
    public function getWeatherByCoordinates(float $latitude, float $longitude, ?int $altitude = null): ?array
    {
        $this->validateCoordinates($latitude, $longitude);
        $this->validateAltitude($altitude);

        $weather = $this->weatherService->getCurrentWeather(
            $latitude,
            $longitude,
            $altitude
        );

        if (! $weather) {
            return null;
        }

        return [
            'current' => $weather,
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'altitude' => $altitude,
                'name' => null,
            ],
        ];
    }

    /**
     * Get forecast by address
     *
     * @param  string  $address  Full address (e.g., "Bergen, Norway")
     * @param  int|null  $altitude  Altitude in meters (optional, -500 to 9000)
     * @param  bool  $complete  Use complete endpoint with all data (default: false for compact)
     * @return array|null Returns forecast data array or null on failure
     *
     * @throws \InvalidArgumentException If address is invalid
     */
    public function getForecastByAddress(string $address, ?int $altitude = null, bool $complete = false): ?array
    {
        $this->validateAddress($address);
        $this->validateAltitude($altitude);

        $geocoded = $this->geocodingService->geocode($address);

        if (! $geocoded) {
            return null;
        }

        $forecast = $this->weatherService->getForecast(
            $geocoded['latitude'],
            $geocoded['longitude'],
            $altitude,
            $complete
        );

        if (! $forecast) {
            return null;
        }

        return [
            'forecast' => $forecast,
            'location' => [
                'latitude' => $geocoded['latitude'],
                'longitude' => $geocoded['longitude'],
                'altitude' => $altitude,
                'name' => $geocoded['display_name'],
            ],
        ];
    }

    /**
     * Get forecast by coordinates
     *
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @param  int|null  $altitude  Altitude in meters (optional, -500 to 9000)
     * @param  bool  $complete  Use complete endpoint with all data (default: false for compact)
     * @return array|null Returns forecast data array or null on failure
     *
     * @throws \InvalidArgumentException If coordinates are invalid
     */
    public function getForecastByCoordinates(float $latitude, float $longitude, ?int $altitude = null, bool $complete = false): ?array
    {
        $this->validateCoordinates($latitude, $longitude);
        $this->validateAltitude($altitude);

        $forecast = $this->weatherService->getForecast(
            $latitude,
            $longitude,
            $altitude,
            $complete
        );

        if (! $forecast) {
            return null;
        }

        return [
            'forecast' => $forecast,
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'altitude' => $altitude,
                'name' => null,
            ],
        ];
    }

    /**
     * Validate address parameter
     *
     * @throws \InvalidArgumentException
     */
    private function validateAddress(string $address): void
    {
        if (empty(trim($address))) {
            throw new \InvalidArgumentException('Address cannot be empty');
        }

        if (strlen($address) < 3) {
            throw new \InvalidArgumentException('Address must be at least 3 characters long');
        }

        if (strlen($address) > 255) {
            throw new \InvalidArgumentException('Address cannot exceed 255 characters');
        }
    }

    /**
     * Validate coordinates
     *
     * @throws \InvalidArgumentException
     */
    private function validateCoordinates(float $latitude, float $longitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180');
        }
    }

    /**
     * Validate altitude parameter
     *
     * @throws \InvalidArgumentException
     */
    private function validateAltitude(?int $altitude): void
    {
        if ($altitude !== null && ($altitude < -500 || $altitude > 9000)) {
            throw new \InvalidArgumentException('Altitude must be between -500 and 9000 meters');
        }
    }

    /**
     * Get sunrise/sunset data by address
     *
     * @param  string  $address  Full address (e.g., "Oslo, Norway")
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @param  int  $offset  Timezone offset in hours (default: 0 for UTC)
     * @return array|null Returns sun data array or null on failure
     *
     * @throws \InvalidArgumentException If address is invalid
     */
    public function getSunByAddress(string $address, ?string $date = null, int $offset = 0): ?array
    {
        $this->validateAddress($address);

        $geocoded = $this->geocodingService->geocode($address);

        if (! $geocoded) {
            return null;
        }

        $sunData = $this->sunService->getSunData(
            $geocoded['latitude'],
            $geocoded['longitude'],
            $date,
            $offset
        );

        if (! $sunData) {
            return null;
        }

        return [
            'sun' => $sunData,
            'location' => [
                'latitude' => $geocoded['latitude'],
                'longitude' => $geocoded['longitude'],
                'name' => $geocoded['display_name'],
            ],
        ];
    }

    /**
     * Get sunrise/sunset data by coordinates
     *
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @param  int  $offset  Timezone offset in hours (default: 0 for UTC)
     * @return array|null Returns sun data array or null on failure
     *
     * @throws \InvalidArgumentException If coordinates are invalid
     */
    public function getSunByCoordinates(float $latitude, float $longitude, ?string $date = null, int $offset = 0): ?array
    {
        $this->validateCoordinates($latitude, $longitude);

        $sunData = $this->sunService->getSunData(
            $latitude,
            $longitude,
            $date,
            $offset
        );

        if (! $sunData) {
            return null;
        }

        return [
            'sun' => $sunData,
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => null,
            ],
        ];
    }

    /**
     * Get moon phase and rise/set data by address
     *
     * @param  string  $address  Full address (e.g., "Oslo, Norway")
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @param  int  $offset  Timezone offset in hours (default: 0 for UTC)
     * @return array|null Returns moon data array or null on failure
     *
     * @throws \InvalidArgumentException If address is invalid
     */
    public function getMoonByAddress(string $address, ?string $date = null, int $offset = 0): ?array
    {
        $this->validateAddress($address);

        $geocoded = $this->geocodingService->geocode($address);

        if (! $geocoded) {
            return null;
        }

        $moonData = $this->moonService->getMoonData(
            $geocoded['latitude'],
            $geocoded['longitude'],
            $date,
            $offset
        );

        if (! $moonData) {
            return null;
        }

        return [
            'moon' => $moonData,
            'location' => [
                'latitude' => $geocoded['latitude'],
                'longitude' => $geocoded['longitude'],
                'name' => $geocoded['display_name'],
            ],
        ];
    }

    /**
     * Get moon phase and rise/set data by coordinates
     *
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @param  int  $offset  Timezone offset in hours (default: 0 for UTC)
     * @return array|null Returns moon data array or null on failure
     *
     * @throws \InvalidArgumentException If coordinates are invalid
     */
    public function getMoonByCoordinates(float $latitude, float $longitude, ?string $date = null, int $offset = 0): ?array
    {
        $this->validateCoordinates($latitude, $longitude);

        $moonData = $this->moonService->getMoonData(
            $latitude,
            $longitude,
            $date,
            $offset
        );

        if (! $moonData) {
            return null;
        }

        return [
            'moon' => $moonData,
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => null,
            ],
        ];
    }
}
