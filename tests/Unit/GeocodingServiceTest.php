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
