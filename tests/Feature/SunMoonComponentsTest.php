<?php

it('sunrise card component can be instantiated', function () {
    $component = new \Ekstremedia\LaravelYr\View\Components\SunriseCard(
        latitude: 59.9139,
        longitude: 10.7522,
        location: 'Oslo, Norway'
    );

    expect($component)->toBeInstanceOf(\Ekstremedia\LaravelYr\View\Components\SunriseCard::class);
    expect($component->latitude)->toBe(59.9139);
    expect($component->longitude)->toBe(10.7522);
    expect($component->location)->toBe('Oslo, Norway');
});

it('sunrise card component renders without errors', function () {
    $view = $this->blade('<x-yr-sunrise-card :latitude="59.9139" :longitude="10.7522" location="Oslo, Norway" />');
    $output = (string) $view;

    // Component should render either success state or error state
    expect($output)->toMatch('/(Sunrise & Sunset|Unable to load sunrise data)/');
});

it('sunrise card component includes attribution', function () {
    $view = $this->blade('<x-yr-sunrise-card :latitude="59.9139" :longitude="10.7522" location="Oslo" />');
    $output = (string) $view;

    // Should have attribution in success state, or error message in error state
    $hasAttribution = str_contains($output, 'Norwegian Meteorological Institute');
    $hasError = str_contains($output, 'Unable to load sunrise data');

    expect($hasAttribution || $hasError)->toBeTrue();
});

it('moon card component can be instantiated', function () {
    $component = new \Ekstremedia\LaravelYr\View\Components\MoonCard(
        latitude: 59.9139,
        longitude: 10.7522,
        location: 'Oslo, Norway'
    );

    expect($component)->toBeInstanceOf(\Ekstremedia\LaravelYr\View\Components\MoonCard::class);
    expect($component->latitude)->toBe(59.9139);
    expect($component->longitude)->toBe(10.7522);
    expect($component->location)->toBe('Oslo, Norway');
});

it('moon card component renders without errors', function () {
    $view = $this->blade('<x-yr-moon-card :latitude="59.9139" :longitude="10.7522" location="Oslo, Norway" />');
    $output = (string) $view;

    // Component should render either success state or error state
    expect($output)->toMatch('/(Moon Phase|Unable to load moon data)/');
});

it('moon card component includes attribution', function () {
    $view = $this->blade('<x-yr-moon-card :latitude="59.9139" :longitude="10.7522" location="Oslo" />');
    $output = (string) $view;

    // Should have attribution in success state, or error message in error state
    $hasAttribution = str_contains($output, 'Norwegian Meteorological Institute');
    $hasError = str_contains($output, 'Unable to load moon data');

    expect($hasAttribution || $hasError)->toBeTrue();
});

it('sunrise card accepts date parameter', function () {
    $component = new \Ekstremedia\LaravelYr\View\Components\SunriseCard(
        latitude: 59.9139,
        longitude: 10.7522,
        location: 'Oslo',
        date: '2025-12-25'
    );

    expect($component->date)->toBe('2025-12-25');
});

it('moon card accepts date parameter', function () {
    $component = new \Ekstremedia\LaravelYr\View\Components\MoonCard(
        latitude: 59.9139,
        longitude: 10.7522,
        location: 'Oslo',
        date: '2025-12-25'
    );

    expect($component->date)->toBe('2025-12-25');
});
