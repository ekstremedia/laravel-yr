<?php

namespace YourVendor\LaravelYr\View\Components;

use Carbon\Carbon;
use Illuminate\View\Component;
use YourVendor\LaravelYr\Services\YrWeatherService;

class ForecastCard extends Component
{
    public ?array $forecast;

    public string $location;

    public YrWeatherService $weatherService;

    public int $days;

    public float $latitude;

    public float $longitude;

    public function __construct(
        float $latitude,
        float $longitude,
        string $location = 'Unknown Location',
        int $days = 5
    ) {
        $this->weatherService = app(YrWeatherService::class);
        $this->forecast = $this->weatherService->getForecast($latitude, $longitude);
        $this->location = $location;
        $this->days = $days;
        $this->latitude = round($latitude, 4);
        $this->longitude = round($longitude, 4);
    }

    public function render()
    {
        return view('laravel-yr::components.forecast-card');
    }

    public function getDailyForecast(): array
    {
        if (! $this->forecast || empty($this->forecast['timeseries'])) {
            return [];
        }

        $daily = [];

        foreach ($this->forecast['timeseries'] as $entry) {
            $time = Carbon::parse($entry['time']);
            $dateKey = $time->format('Y-m-d');

            if (! isset($daily[$dateKey])) {
                $daily[$dateKey] = [
                    'date' => $time,
                    'temps' => [],
                    'precipitation' => [],
                    'symbols' => [],
                    'wind_speeds' => [],
                    'timeseries' => [],
                ];
            }

            if ($entry['temperature'] !== null) {
                $daily[$dateKey]['temps'][] = $entry['temperature'];
            }

            if (($entry['precipitation_amount'] ?? null) !== null) {
                $daily[$dateKey]['precipitation'][] = $entry['precipitation_amount'];
            }

            if ($entry['symbol_code']) {
                $daily[$dateKey]['symbols'][] = $entry['symbol_code'];
            }

            if ($entry['wind_speed'] !== null) {
                $daily[$dateKey]['wind_speeds'][] = $entry['wind_speed'];
            }

            // Add full timeseries entry
            $daily[$dateKey]['timeseries'][] = [
                'time' => $time,
                'temperature' => $entry['temperature'],
                'feels_like' => $entry['feels_like'],
                'symbol_code' => $entry['symbol_code'],
                'precipitation_amount' => $entry['precipitation_amount'] ?? 0,
                'wind_speed' => $entry['wind_speed'],
                'wind_direction' => $entry['wind_direction'],
                'humidity' => $entry['humidity'],
            ];
        }

        $result = [];
        foreach (array_slice(array_keys($daily), 0, $this->days) as $dateKey) {
            $day = $daily[$dateKey];
            $result[] = [
                'date' => $day['date'],
                'temp_high' => ! empty($day['temps']) ? round(max($day['temps']), 1) : null,
                'temp_low' => ! empty($day['temps']) ? round(min($day['temps']), 1) : null,
                'precipitation' => ! empty($day['precipitation']) ? round(array_sum($day['precipitation']), 1) : 0,
                'symbol_code' => $this->getMostFrequentSymbol($day['symbols']),
                'wind_speed_avg' => ! empty($day['wind_speeds']) ? round(array_sum($day['wind_speeds']) / count($day['wind_speeds']), 1) : null,
                'timeseries' => $day['timeseries'],
            ];
        }

        return $result;
    }

    private function getMostFrequentSymbol(array $symbols): ?string
    {
        if (empty($symbols)) {
            return null;
        }

        $counts = array_count_values($symbols);
        arsort($counts);

        return array_key_first($counts);
    }
}
