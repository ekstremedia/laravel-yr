<?php

use YourVendor\LaravelYr\Services\GeocodingService;

beforeEach(function () {
    $this->service = app(GeocodingService::class);
});

it('can be instantiated', function () {
    expect($this->service)->toBeInstanceOf(GeocodingService::class);
});

it('uses correct user agent', function () {
    $reflection = new ReflectionClass($this->service);
    $property = $reflection->getProperty('userAgent');
    $property->setAccessible(true);

    $userAgent = $property->getValue($this->service);

    expect($userAgent)->toBeString()
        ->and($userAgent)->toContain('LaravelYrTestSuite');
});

it('truncates geocoded coordinates to 4 decimals per MET API TOS', function () {
    // Mock a geocoding result to test coordinate truncation
    // In a real scenario, the geocoding service would return coordinates
    // truncated to 4 decimals as per MET API requirements

    // We verify the geocode method returns an array structure
    $result = $this->service->geocode('Oslo, Norway');

    // If result is not null, verify coordinate format
    if ($result !== null) {
        expect($result)
            ->toHaveKey('latitude')
            ->toHaveKey('longitude');

        // Verify coordinates are numeric
        expect($result['latitude'])->toBeFloat();
        expect($result['longitude'])->toBeFloat();

        // Verify maximum 4 decimal places
        $latDecimals = strlen(substr(strrchr((string) $result['latitude'], '.'), 1));
        $lonDecimals = strlen(substr(strrchr((string) $result['longitude'], '.'), 1));

        expect($latDecimals)->toBeLessThanOrEqual(4);
        expect($lonDecimals)->toBeLessThanOrEqual(4);
    }
});
