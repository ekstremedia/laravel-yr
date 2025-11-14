<?php

namespace YourVendor\LaravelYr\View\Components;

use Illuminate\View\Component;
use YourVendor\LaravelYr\Services\YrWeatherService;

class WeatherCard extends Component
{
    public ?array $weather;

    public string $location;

    public YrWeatherService $weatherService;

    public function __construct(
        float $latitude,
        float $longitude,
        string $location = 'Unknown Location'
    ) {
        $this->weatherService = app(YrWeatherService::class);
        $this->weather = $this->weatherService->getCurrentWeather($latitude, $longitude);
        $this->location = $location;
    }

    public function render()
    {
        return view('laravel-yr::components.weather-card');
    }

    public function getTemperature(): ?string
    {
        return $this->weather['temperature'] !== null
            ? round($this->weather['temperature'], 1).'°C'
            : null;
    }

    public function getWindSpeed(): ?string
    {
        return $this->weather['wind_speed'] !== null
            ? round($this->weather['wind_speed'], 1).' m/s'
            : null;
    }

    public function getSymbolUrl(): ?string
    {
        if ($this->weather['symbol_code'] ?? null) {
            return $this->weatherService->getSymbolUrl($this->weather['symbol_code']);
        }

        return null;
    }
}
