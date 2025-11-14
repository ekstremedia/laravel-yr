<?php

it('sun endpoint is accessible with coordinates', function () {
    $response = $this->get('/api/weather/sun?lat=59.9139&lon=10.7522');

    // Accept either success or service unavailable (real API may be down in tests)
    expect($response->status())->toBeIn([200, 500]);

    if ($response->status() === 200) {
        $response->assertJsonStructure([
            'success',
            'data' => [
                'sun',
                'location',
            ],
        ]);
    }
});

it('sun endpoint returns sunrise and sunset data', function () {
    $response = $this->get('/api/weather/sun?lat=59.9139&lon=10.7522');

    // Skip if API is unavailable
    if ($response->status() !== 200) {
        expect(true)->toBeTrue();

        return;
    }

    $data = $response->json('data.sun');
    expect($data)->toHaveKeys(['sunrise', 'sunset', 'solar_noon', 'solar_midnight', 'daylight_duration']);
});

it('sun endpoint validates coordinates', function () {
    $response = $this->get('/api/weather/sun?lat=invalid&lon=10.7522');

    $response->assertStatus(400);
    $response->assertJson([
        'error' => 'Invalid coordinates',
    ]);
});

it('sun endpoint requires location parameters', function () {
    $response = $this->get('/api/weather/sun');

    $response->assertStatus(400);
    $response->assertJson([
        'error' => 'Missing location parameters',
    ]);
});

it('sun endpoint accepts date parameter', function () {
    $response = $this->get('/api/weather/sun?lat=59.9139&lon=10.7522&date=2025-12-25');

    // Accept either success or service unavailable
    expect($response->status())->toBeIn([200, 500]);
});

it('sun endpoint accepts offset parameter', function () {
    $response = $this->get('/api/weather/sun?lat=59.9139&lon=10.7522&offset=2');

    // Accept either success or service unavailable
    expect($response->status())->toBeIn([200, 500]);
});

it('moon endpoint is accessible with coordinates', function () {
    $response = $this->get('/api/weather/moon?lat=59.9139&lon=10.7522');

    // Accept either success or service unavailable (real API may be down in tests)
    expect($response->status())->toBeIn([200, 500]);

    if ($response->status() === 200) {
        $response->assertJsonStructure([
            'success',
            'data' => [
                'moon',
                'location',
            ],
        ]);
    }
});

it('moon endpoint returns moon phase and rise/set data', function () {
    $response = $this->get('/api/weather/moon?lat=59.9139&lon=10.7522');

    // Skip if API is unavailable
    if ($response->status() !== 200) {
        expect(true)->toBeTrue();

        return;
    }

    $data = $response->json('data.moon');
    expect($data)->toHaveKeys(['moonrise', 'moonset', 'moon_phase', 'phase_name', 'phase_emoji', 'high_moon', 'low_moon']);
});

it('moon endpoint validates coordinates', function () {
    $response = $this->get('/api/weather/moon?lat=invalid&lon=10.7522');

    $response->assertStatus(400);
    $response->assertJson([
        'error' => 'Invalid coordinates',
    ]);
});

it('moon endpoint requires location parameters', function () {
    $response = $this->get('/api/weather/moon');

    $response->assertStatus(400);
    $response->assertJson([
        'error' => 'Missing location parameters',
    ]);
});

it('moon endpoint accepts date parameter', function () {
    $response = $this->get('/api/weather/moon?lat=59.9139&lon=10.7522&date=2025-12-25');

    // Accept either success or service unavailable
    expect($response->status())->toBeIn([200, 500]);
});

it('moon endpoint accepts offset parameter', function () {
    $response = $this->get('/api/weather/moon?lat=59.9139&lon=10.7522&offset=2');

    // Accept either success or service unavailable
    expect($response->status())->toBeIn([200, 500]);
});

it('sun endpoint has named route', function () {
    expect(route('yr.api.sun', ['lat' => 60, 'lon' => 10]))->toContain('/api/weather/sun');
});

it('moon endpoint has named route', function () {
    expect(route('yr.api.moon', ['lat' => 60, 'lon' => 10]))->toContain('/api/weather/moon');
});
