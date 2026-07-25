<?php

use Ekstremedia\LaravelYr\View\Components\ForecastCard;
use Ekstremedia\LaravelYr\View\Components\WeatherCard;
use Illuminate\Support\Facades\Blade;

it('weather card blade component renders via Blade syntax', function () {
    // Create a test blade file that uses the component
    $blade = <<<'BLADE'
<x-yr-weather-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
/>
BLADE;

    $rendered = Blade::render($blade, []);

    expect($rendered)->toBeString();
    expect($rendered)->toContain('yr-weather-card');
    // Should NOT contain old namespace in compiled output
    expect($rendered)->not->toContain('YourVendor');
    expect($rendered)->not->toContain('your-vendor');
});

it('forecast card blade component renders via Blade syntax', function () {
    // Create a test blade file that uses the component
    $blade = <<<'BLADE'
<x-yr-forecast-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo"
    :days="5"
/>
BLADE;

    $rendered = Blade::render($blade, []);

    expect($rendered)->toBeString();
    expect($rendered)->toContain('yr-forecast-card');
    // Should NOT contain old namespace in compiled output
    expect($rendered)->not->toContain('YourVendor');
    expect($rendered)->not->toContain('your-vendor');
});

it('demo view renders without errors', function () {
    config(['yr.enable_demo_route' => true]);

    $response = $this->get('/yr');

    $response->assertStatus(200);
    $response->assertSee('Sortland, Norway', false);
    // Ensure no old namespace references in rendered HTML
    expect($response->getContent())->not->toContain('YourVendor');
});

it('weather card component can be instantiated with correct namespace', function () {
    $component = new WeatherCard(
        latitude: 59.9139,
        longitude: 10.7522,
        location: 'Oslo'
    );

    expect($component)->toBeInstanceOf(WeatherCard::class);
    expect($component->location)->toBe('Oslo');
});

it('forecast card component can be instantiated with correct namespace', function () {
    $component = new ForecastCard(
        latitude: 59.9139,
        longitude: 10.7522,
        location: 'Oslo',
        days: 5
    );

    expect($component)->toBeInstanceOf(ForecastCard::class);
    expect($component->location)->toBe('Oslo');
    expect($component->days)->toBe(5);
});
