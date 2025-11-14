<?php

use YourVendor\LaravelYr\Services\YrWeatherService;

beforeEach(function () {
    $this->service = app(YrWeatherService::class);
});

it('generates correct weather symbol URL', function () {
    $url = $this->service->getSymbolUrl('clearsky_day');

    expect($url)
        ->toBeString()
        ->toContain('api.met.no')
        ->toContain('clearsky_day');
});

it('generates correct weather symbol URL with night variant', function () {
    $url = $this->service->getSymbolUrl('partlycloudy_night');

    expect($url)
        ->toBeString()
        ->toContain('partlycloudy_night');
});

it('calculates feels like temperature correctly for cold weather', function () {
    // This is a white-box test - we're testing the internal calculation
    // Create a reflection to test the private method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('calculateFeelsLike');
    $method->setAccessible(true);

    // Cold temperature with wind should produce wind chill
    $result = $method->invoke($this->service, 5.0, 5.0, 50.0);

    expect($result)->toBeFloat()
        ->and($result)->toBeLessThan(5.0); // Should feel colder
});

it('calculates feels like temperature correctly for hot weather', function () {
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('calculateFeelsLike');
    $method->setAccessible(true);

    // Hot temperature with high humidity should produce heat index
    $result = $method->invoke($this->service, 30.0, 2.0, 80.0);

    expect($result)->toBeFloat()
        ->and($result)->toBeGreaterThan(30.0); // Should feel hotter
});

it('returns same temperature when no adjustment needed', function () {
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('calculateFeelsLike');
    $method->setAccessible(true);

    // Mild temperature with low wind
    $result = $method->invoke($this->service, 15.0, 1.0, 50.0);

    expect($result)->toBe(15.0); // Should be unchanged
});
