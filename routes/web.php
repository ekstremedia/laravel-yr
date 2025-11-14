<?php

use Ekstremedia\LaravelYr\Services\GeocodingService;
use Illuminate\Support\Facades\Route;

Route::get('/yr', function (GeocodingService $geocodingService) {
    // Default to Sortland, Norway
    $latitude = 68.7044;
    $longitude = 15.4140;
    $location = 'Sortland, Norway';
    $error = null;

    // Check if location search is provided
    if (request()->has('location') && request('location')) {
        $searchLocation = request('location');
        $geocoded = $geocodingService->geocode($searchLocation);

        if ($geocoded) {
            $latitude = $geocoded['latitude'];
            $longitude = $geocoded['longitude'];
            $location = $geocoded['display_name'] ?? $searchLocation;
        } else {
            $error = "Could not find location: {$searchLocation}";
        }
    }
    // Check if manual coordinates are provided
    elseif (request()->has('latitude') && request()->has('longitude')) {
        $latitude = (float) request('latitude');
        $longitude = (float) request('longitude');
        $location = request('location_name', "{$latitude}°N, {$longitude}°E");
    }

    return view('laravel-yr::demo', compact('latitude', 'longitude', 'location', 'error'));
})->name('yr.demo');
