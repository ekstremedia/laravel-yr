<?php

use Illuminate\Support\Facades\Route;
use YourVendor\LaravelYr\Http\Controllers\WeatherController;

beforeEach(function () {
    Route::get('/api/weather/current', [WeatherController::class, 'current']);
    Route::get('/api/weather/forecast', [WeatherController::class, 'forecast']);
});

it('returns error for missing location parameters', function () {
    $response = $this->get('/api/weather/current');

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'Missing location parameters',
        ]);
});

it('returns error for invalid coordinates', function () {
    $response = $this->get('/api/weather/current?lat=999&lon=999');

    $response->assertStatus(400)
        ->assertJsonPath('error', 'Invalid coordinates');
});

it('returns error for invalid latitude', function () {
    $response = $this->get('/api/weather/current?lat=-100&lon=10');

    $response->assertStatus(400);
});

it('returns error for invalid longitude', function () {
    $response = $this->get('/api/weather/current?lat=59&lon=200');

    $response->assertStatus(400);
});

it('validates altitude range', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522&altitude=10000');

    $response->assertStatus(400);
});

it('accepts valid coordinates', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522');

    // May return 500 if API is unavailable, but should not return validation errors
    expect($response->status())->not->toBe(400);
});

it('accepts valid altitude parameter', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522&altitude=90');

    // May return 500 if API is unavailable, but should not return validation errors
    expect($response->status())->not->toBe(400);
});

it('handles forecast endpoint', function () {
    $response = $this->get('/api/weather/forecast?lat=59.9139&lon=10.7522');

    // May return 500 if API is unavailable, but should not return validation errors
    expect($response->status())->not->toBe(400);
});
