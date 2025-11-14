<?php

use YourVendor\LaravelYr\Services\GeocodingService;

beforeEach(function () {
    $this->service = app(GeocodingService::class);
});

it('can geocode an address', function () {
    $result = $this->service->geocode('Oslo, Norway');

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['latitude', 'longitude', 'display_name'])
        ->and($result['latitude'])->toBeFloat()
        ->and($result['longitude'])->toBeFloat()
        ->and($result['display_name'])->toBeString();
});

it('returns coordinates for Oslo', function () {
    $result = $this->service->geocode('Oslo, Norway');

    expect($result['latitude'])->toBeGreaterThan(59.8)
        ->and($result['latitude'])->toBeLessThan(60.0)
        ->and($result['longitude'])->toBeGreaterThan(10.6)
        ->and($result['longitude'])->toBeLessThan(10.8);
});

it('returns null for invalid address', function () {
    $result = $this->service->geocode('InvalidCityThatDoesNotExist12345');

    expect($result)->toBeNull();
});

it('caches geocoding results', function () {
    $address = 'Bergen, Norway';

    // First call
    $result1 = $this->service->geocode($address);

    // Second call should use cache
    $result2 = $this->service->geocode($address);

    expect($result1)->toEqual($result2);
});

it('can reverse geocode coordinates', function () {
    $result = $this->service->reverseGeocode(59.9139, 10.7522);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['display_name'])
        ->and($result['display_name'])->toBeString()
        ->and($result['display_name'])->toContain('Oslo');
});

it('includes address details in geocode result', function () {
    $result = $this->service->geocode('Oslo, Norway');

    expect($result)->toHaveKey('address')
        ->and($result['address'])->toBeArray();
});
