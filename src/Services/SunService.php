<?php

namespace Ekstremedia\LaravelYr\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SunService
{
    private string $baseUrl = 'https://api.met.no/weatherapi/sunrise/3.0';

    private string $userAgent;

    private int $cacheTtl;

    public function __construct()
    {
        $this->userAgent = config('yr.user_agent', 'Laravel-Yr/1.0 (contact@example.com)');
        $this->cacheTtl = config('yr.cache_ttl', 3600);
    }

    /**
     * Get sunrise/sunset data for given coordinates and date
     *
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @param  int  $offset  Timezone offset in hours (default: 0 for UTC)
     */
    public function getSunData(float $latitude, float $longitude, ?string $date = null, int $offset = 0): ?array
    {
        // Truncate coordinates to 4 decimals as required by MET.no
        $latitude = round($latitude, 4);
        $longitude = round($longitude, 4);

        // Use today's date if not specified
        if (! $date) {
            $date = now()->format('Y-m-d');
        }

        $cacheKey = "yr_sun_{$latitude}_{$longitude}_{$date}_{$offset}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude, $date, $offset, $cacheKey) {
            try {
                $params = [
                    'lat' => $latitude,
                    'lon' => $longitude,
                ];

                // Only add date if specified
                if ($date) {
                    $params['date'] = $date;
                }

                // Only add offset if non-zero
                if ($offset !== 0) {
                    $params['offset'] = sprintf('%+03d:00', $offset);
                }

                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])
                    ->timeout(10)
                    ->get("{$this->baseUrl}/sun", $params);

                if (! $response->successful()) {
                    \Log::error('SunService API failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => "{$this->baseUrl}/sun",
                        'params' => ['lat' => $latitude, 'lon' => $longitude, 'date' => $date, 'offset' => $offset],
                    ]);

                    return;
                }

                $data = $response->json();

                // Check for Expires header and update cache TTL accordingly
                if ($response->hasHeader('Expires')) {
                    $expires = strtotime($response->header('Expires'));
                    $newTtl = max($expires - time(), 60); // Minimum 60 seconds

                    // Re-cache with updated TTL
                    Cache::put($cacheKey, $data, $newTtl);
                }

                return $this->formatSunData($data);
            } catch (\Exception $e) {
                \Log::error('SunService exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return;
            }
        });
    }

    /**
     * Format sun data to a more usable structure
     */
    private function formatSunData(array $data): array
    {
        $properties = $data['properties'] ?? [];

        return [
            'sunrise' => [
                'time' => $properties['sunrise']['time'] ?? null,
                'azimuth' => $properties['sunrise']['azimuth'] ?? null,
            ],
            'sunset' => [
                'time' => $properties['sunset']['time'] ?? null,
                'azimuth' => $properties['sunset']['azimuth'] ?? null,
            ],
            'solar_noon' => [
                'time' => $properties['solarnoon']['time'] ?? null,
                'elevation' => $properties['solarnoon']['disc_centre_elevation'] ?? null,
                'visible' => $properties['solarnoon']['visible'] ?? false,
            ],
            'solar_midnight' => [
                'time' => $properties['solarmidnight']['time'] ?? null,
                'elevation' => $properties['solarmidnight']['disc_centre_elevation'] ?? null,
                'visible' => $properties['solarmidnight']['visible'] ?? false,
            ],
            'daylight_duration' => $this->calculateDaylightDuration(
                $properties['sunrise']['time'] ?? null,
                $properties['sunset']['time'] ?? null
            ),
            'coordinates' => $data['geometry']['coordinates'] ?? [$properties['longitude'] ?? null, $properties['latitude'] ?? null],
            'interval' => $data['when']['interval'] ?? null,
        ];
    }

    /**
     * Calculate daylight duration in hours and minutes
     */
    private function calculateDaylightDuration(?string $sunrise, ?string $sunset): ?array
    {
        if (! $sunrise || ! $sunset) {
            return null;
        }

        try {
            $sunriseTime = new \DateTime($sunrise);
            $sunsetTime = new \DateTime($sunset);
            $diff = $sunriseTime->diff($sunsetTime);

            return [
                'hours' => $diff->h,
                'minutes' => $diff->i,
                'total_minutes' => ($diff->h * 60) + $diff->i,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format time to human-readable format
     */
    public function formatTime(?string $time, string $format = 'H:i'): ?string
    {
        if (! $time) {
            return null;
        }

        try {
            return (new \DateTime($time))->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
}
