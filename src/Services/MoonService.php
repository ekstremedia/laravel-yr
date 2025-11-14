<?php

namespace Ekstremedia\LaravelYr\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MoonService
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
     * Get moon phase and rise/set data for given coordinates and date
     *
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @param  int  $offset  Timezone offset in hours (default: 0 for UTC)
     */
    public function getMoonData(float $latitude, float $longitude, ?string $date = null, int $offset = 0): ?array
    {
        // Truncate coordinates to 4 decimals as required by MET.no
        $latitude = round($latitude, 4);
        $longitude = round($longitude, 4);

        // Use today's date if not specified
        if (! $date) {
            $date = now()->format('Y-m-d');
        }

        $cacheKey = "yr_moon_{$latitude}_{$longitude}_{$date}_{$offset}";

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
                    ->get("{$this->baseUrl}/moon", $params);

                if (! $response->successful()) {
                    \Log::error('MoonService API failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => "{$this->baseUrl}/moon",
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

                return $this->formatMoonData($data);
            } catch (\Exception $e) {
                \Log::error('MoonService exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return;
            }
        });
    }

    /**
     * Format moon data to a more usable structure
     */
    private function formatMoonData(array $data): array
    {
        $properties = $data['properties'] ?? [];

        return [
            'moonrise' => [
                'time' => $properties['moonrise']['time'] ?? null,
                'azimuth' => $properties['moonrise']['azimuth'] ?? null,
            ],
            'moonset' => [
                'time' => $properties['moonset']['time'] ?? null,
                'azimuth' => $properties['moonset']['azimuth'] ?? null,
            ],
            'high_moon' => [
                'time' => $properties['high_moon']['time'] ?? null,
                'elevation' => $properties['high_moon']['disc_centre_elevation'] ?? null,
                'visible' => $properties['high_moon']['visible'] ?? false,
            ],
            'low_moon' => [
                'time' => $properties['low_moon']['time'] ?? null,
                'elevation' => $properties['low_moon']['disc_centre_elevation'] ?? null,
                'visible' => $properties['low_moon']['visible'] ?? false,
            ],
            'moon_phase' => $properties['moonphase'] ?? null,
            'phase_name' => $this->getMoonPhaseName($properties['moonphase'] ?? null),
            'phase_emoji' => $this->getMoonPhaseEmoji($properties['moonphase'] ?? null),
            'coordinates' => $data['geometry']['coordinates'] ?? [$properties['longitude'] ?? null, $properties['latitude'] ?? null],
            'interval' => $data['when']['interval'] ?? null,
        ];
    }

    /**
     * Get moon phase name from degree value
     */
    public function getMoonPhaseName(?float $phase): string
    {
        if ($phase === null) {
            return 'Unknown';
        }

        // Normalize to 0-360
        $phase = fmod($phase, 360);
        if ($phase < 0) {
            $phase += 360;
        }

        if ($phase < 22.5 || $phase >= 337.5) {
            return 'New Moon';
        } elseif ($phase < 67.5) {
            return 'Waxing Crescent';
        } elseif ($phase < 112.5) {
            return 'First Quarter';
        } elseif ($phase < 157.5) {
            return 'Waxing Gibbous';
        } elseif ($phase < 202.5) {
            return 'Full Moon';
        } elseif ($phase < 247.5) {
            return 'Waning Gibbous';
        } elseif ($phase < 292.5) {
            return 'Last Quarter';
        } else {
            return 'Waning Crescent';
        }
    }

    /**
     * Get moon phase emoji
     */
    public function getMoonPhaseEmoji(?float $phase): string
    {
        if ($phase === null) {
            return '🌑';
        }

        // Normalize to 0-360
        $phase = fmod($phase, 360);
        if ($phase < 0) {
            $phase += 360;
        }

        if ($phase < 22.5 || $phase >= 337.5) {
            return '🌑'; // New Moon
        } elseif ($phase < 67.5) {
            return '🌒'; // Waxing Crescent
        } elseif ($phase < 112.5) {
            return '🌓'; // First Quarter
        } elseif ($phase < 157.5) {
            return '🌔'; // Waxing Gibbous
        } elseif ($phase < 202.5) {
            return '🌕'; // Full Moon
        } elseif ($phase < 247.5) {
            return '🌖'; // Waning Gibbous
        } elseif ($phase < 292.5) {
            return '🌗'; // Last Quarter
        } else {
            return '🌘'; // Waning Crescent
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
