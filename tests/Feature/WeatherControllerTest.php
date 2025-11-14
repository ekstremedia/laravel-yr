<?php

use Illuminate\Support\Facades\Route;
use YourVendor\LaravelYr\Http\Controllers\WeatherController;

beforeEach(function () {
    Route::get('/api/weather/current', [WeatherController::class, 'current']);
    Route::get('/api/weather/forecast', [WeatherController::class, 'forecast']);
});

it('can get current weather with coordinates', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'current' => [
                    'time',
                    'temperature',
                    'wind_speed',
                    'humidity',
                ],
                'location' => [
                    'latitude',
                    'longitude',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

it('can get current weather with altitude', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522&altitude=90');

    $response->assertStatus(200)
        ->assertJsonPath('data.location.altitude', 90);
});

it('can get current weather with address', function () {
    $response = $this->get('/api/weather/current?address=Oslo,Norway');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'current',
                'location' => [
                    'latitude',
                    'longitude',
                    'name',
                ],
            ],
        ]);
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

it('can get forecast with coordinates', function () {
    $response = $this->get('/api/weather/forecast?lat=59.9139&lon=10.7522');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'forecast' => [
                    'timeseries',
                    'updated_at',
                ],
                'location',
            ],
        ]);
});

it('can get complete forecast', function () {
    $response = $this->get('/api/weather/forecast?lat=59.9139&lon=10.7522&complete=1');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('can get forecast with address', function () {
    $response = $this->get('/api/weather/forecast?address=Bergen,Norway');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'forecast',
                'location' => [
                    'name',
                ],
            ],
        ]);
});

it('validates altitude range', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522&altitude=10000');

    $response->assertStatus(400);
});

it('returns 404 for invalid address', function () {
    $response = $this->get('/api/weather/current?address=InvalidCityThatDoesNotExist12345');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'Address not found');
});

it('includes all weather data fields', function () {
    $response = $this->get('/api/weather/current?lat=59.9139&lon=10.7522');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'current' => [
                    'time',
                    'temperature',
                    'feels_like',
                    'wind_speed',
                    'wind_direction',
                    'humidity',
                    'pressure',
                    'cloud_coverage',
                    'uv_index',
                    'precipitation_amount',
                    'symbol_code',
                ],
            ],
        ]);
});
