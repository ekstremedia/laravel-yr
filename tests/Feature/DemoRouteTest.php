<?php

use Ekstremedia\LaravelYr\Services\GeocodingService;

beforeEach(function () {
    config(['yr.enable_demo_route' => true]);
});

it('demo route renders with default location', function () {
    $response = $this->get('/yr');

    $response->assertStatus(200);
    $response->assertSee('Sortland, Norway', false);
    $response->assertSee('68.7044', false);
    $response->assertSee('15.414', false);
    $response->assertSee('Change Location', false);
});

it('demo route accepts manual coordinates via query parameters', function () {
    $response = $this->get('/yr?latitude=59.9139&longitude=10.7522&location_name=Oslo');

    $response->assertStatus(200);
    $response->assertSee('Oslo', false);
    $response->assertSee('59.9139', false);
    $response->assertSee('10.7522', false);
});

it('demo route handles location search parameter', function () {
    // Mock the geocoding service
    $this->mock(GeocodingService::class, function ($mock) {
        $mock->shouldReceive('geocode')
            ->with('Oslo, Norway')
            ->once()
            ->andReturn([
                'latitude' => 59.9139,
                'longitude' => 10.7522,
                'display_name' => 'Oslo, Norway',
            ]);
    });

    $response = $this->get('/yr?location=Oslo, Norway');

    $response->assertStatus(200);
    $response->assertSee('Oslo, Norway', false);
    $response->assertSee('59.9139', false);
    $response->assertSee('10.7522', false);
});

it('demo route displays error when location is not found', function () {
    // Mock the geocoding service to return null (not found)
    $this->mock(GeocodingService::class, function ($mock) {
        $mock->shouldReceive('geocode')
            ->with('InvalidCity12345')
            ->once()
            ->andReturn(null);
    });

    $response = $this->get('/yr?location=InvalidCity12345');

    $response->assertStatus(200);
    $response->assertSee('Could not find location: InvalidCity12345', false);
    // Should still render with default location
    $response->assertSee('Sortland, Norway', false);
});

it('demo route includes search form elements', function () {
    $response = $this->get('/yr');

    $response->assertStatus(200);
    $response->assertSee('Search by Location', false);
    $response->assertSee('Manual Coordinates', false);
    $response->assertSee('Currently showing:', false);
    $response->assertSee('<form', false);
    $response->assertSee('name="location"', false);
    $response->assertSee('name="latitude"', false);
    $response->assertSee('name="longitude"', false);
});

it('demo route uses Alpine.js for form toggle', function () {
    $response = $this->get('/yr');

    $response->assertStatus(200);
    $response->assertSee('x-data', false);
    $response->assertSee('x-show', false);
    $response->assertSee('alpinejs', false);
});

it('demo route renders both weather components with dynamic coordinates', function () {
    $response = $this->get('/yr?latitude=60.3913&longitude=5.3221&location_name=Bergen');

    $response->assertStatus(200);
    // Should pass coordinates to components
    $response->assertSee('Bergen', false);
    $response->assertSee('yr-weather-card', false);
    $response->assertSee('yr-forecast-card', false);
    $response->assertSee('60.3913', false);
    $response->assertSee('5.3221', false);
});

it('demo route preserves location search input after submission', function () {
    $response = $this->get('/yr?location=Tokyo');

    $response->assertStatus(200);
    $response->assertSee('value="Tokyo"', false);
});

it('demo route preserves coordinate inputs after submission', function () {
    $response = $this->get('/yr?latitude=35.6762&longitude=139.6503&location_name=Tokyo');

    $response->assertStatus(200);
    $response->assertSee('value="35.6762"', false);
    $response->assertSee('value="139.6503"', false);
    $response->assertSee('value="Tokyo"', false);
});
