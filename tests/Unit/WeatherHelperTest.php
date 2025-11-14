<?php

use Ekstremedia\LaravelYr\Services\WeatherHelper;

beforeEach(function () {
    $this->helper = app(WeatherHelper::class);
});

it('can be instantiated', function () {
    expect($this->helper)->toBeInstanceOf(WeatherHelper::class);
});

it('validates latitude range above maximum', function () {
    $this->helper->getWeatherByCoordinates(91, 10);
})->throws(\InvalidArgumentException::class, 'Latitude must be between -90 and 90');

it('validates latitude range below minimum', function () {
    $this->helper->getWeatherByCoordinates(-91, 10);
})->throws(\InvalidArgumentException::class, 'Latitude must be between -90 and 90');

it('validates longitude range above maximum', function () {
    $this->helper->getWeatherByCoordinates(60, 181);
})->throws(\InvalidArgumentException::class, 'Longitude must be between -180 and 180');

it('validates longitude range below minimum', function () {
    $this->helper->getWeatherByCoordinates(60, -181);
})->throws(\InvalidArgumentException::class, 'Longitude must be between -180 and 180');

it('validates altitude range above maximum', function () {
    $this->helper->getWeatherByCoordinates(60, 10, 10000);
})->throws(\InvalidArgumentException::class, 'Altitude must be between -500 and 9000 meters');

it('validates altitude range below minimum', function () {
    $this->helper->getWeatherByCoordinates(60, 10, -501);
})->throws(\InvalidArgumentException::class, 'Altitude must be between -500 and 9000 meters');

it('validates address minimum length', function () {
    $this->helper->getWeatherByAddress('ab');
})->throws(\InvalidArgumentException::class, 'Address must be at least 3 characters long');

it('validates address cannot be empty', function () {
    $this->helper->getWeatherByAddress('');
})->throws(\InvalidArgumentException::class, 'Address cannot be empty');

it('validates address cannot be only whitespace', function () {
    $this->helper->getWeatherByAddress('   ');
})->throws(\InvalidArgumentException::class, 'Address cannot be empty');

it('validates address maximum length', function () {
    $this->helper->getWeatherByAddress(str_repeat('a', 256));
})->throws(\InvalidArgumentException::class, 'Address cannot exceed 255 characters');

it('accepts valid coordinates', function () {
    // Just verify no exception is thrown when calling with valid coordinates
    // May return null if API unavailable, but shouldn't throw validation error
    try {
        $this->helper->getWeatherByCoordinates(59.9139, 10.7522, 90);
        expect(true)->toBeTrue();
    } catch (\InvalidArgumentException $e) {
        throw $e; // Re-throw validation errors
    } catch (\Exception $e) {
        // Other exceptions (like network errors) are fine for this test
        expect(true)->toBeTrue();
    }
});

it('accepts valid address', function () {
    // Just verify no exception is thrown when calling with valid address
    // May return null if geocoding/API unavailable, but shouldn't throw validation error
    try {
        $this->helper->getWeatherByAddress('Oslo, Norway');
        expect(true)->toBeTrue();
    } catch (\InvalidArgumentException $e) {
        throw $e; // Re-throw validation errors
    } catch (\Exception $e) {
        // Other exceptions (like network errors) are fine for this test
        expect(true)->toBeTrue();
    }
});
