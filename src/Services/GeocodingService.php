<?php

namespace Ekstremedia\LaravelYr\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    private Client $client;

    private string $userAgent;

    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org';

    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;
        $this->client = new Client([
            'base_uri' => self::NOMINATIM_URL,
            'headers' => [
                'User-Agent' => $this->userAgent,
            ],
        ]);
    }

    /**
     * Geocode an address to coordinates
     *
     * @return array|null Returns ['latitude' => float, 'longitude' => float, 'display_name' => string] or null
     */
    public function geocode(string $address): ?array
    {
        $cacheKey = 'geocode_'.md5($address);

        return Cache::remember($cacheKey, 86400 * 7, function () use ($address) {
            try {
                $response = $this->client->get('/search', [
                    'query' => [
                        'q' => $address,
                        'format' => 'json',
                        'limit' => 1,
                        'addressdetails' => 1,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (empty($data)) {
                    return;
                }

                $result = $data[0];

                // Truncate to max 4 decimals per MET API TOS
                return [
                    'latitude' => round((float) $result['lat'], 4),
                    'longitude' => round((float) $result['lon'], 4),
                    'display_name' => $result['display_name'],
                    'address' => $result['address'] ?? [],
                ];
            } catch (GuzzleException $e) {
                report($e);

                return;
            }
        });
    }

    /**
     * Reverse geocode coordinates to an address
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $cacheKey = "reverse_geocode_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 86400 * 7, function () use ($latitude, $longitude) {
            try {
                $response = $this->client->get('/reverse', [
                    'query' => [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'json',
                        'addressdetails' => 1,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (empty($data)) {
                    return;
                }

                return [
                    'display_name' => $data['display_name'],
                    'address' => $data['address'] ?? [],
                ];
            } catch (GuzzleException $e) {
                report($e);

                return;
            }
        });
    }
}
