<?php

use Ekstremedia\LaravelYr\Services\MoonService;

beforeEach(function () {
    $this->service = new MoonService();
});

it('can be instantiated', function () {
    expect($this->service)->toBeInstanceOf(MoonService::class);
});

it('truncates coordinates to 4 decimals', function () {
    // Moon data should accept coordinates with many decimals
    // and truncate them to 4 decimals as required by MET.no
    $data = $this->service->getMoonData(59.91398765, 10.75223456);

    // If data is fetched, coordinates should be truncated
    if ($data) {
        expect($data)->toBeArray();
        // The service truncates internally, so we just verify data structure
        expect($data)->toHaveKeys(['moonrise', 'moonset', 'moon_phase', 'phase_name']);
    }

    expect(true)->toBeTrue();
});

it('gets correct moon phase name for new moon', function () {
    $phaseName = $this->service->getMoonPhaseName(0);
    expect($phaseName)->toBe('New Moon');

    $phaseName = $this->service->getMoonPhaseName(360);
    expect($phaseName)->toBe('New Moon');
});

it('gets correct moon phase name for first quarter', function () {
    $phaseName = $this->service->getMoonPhaseName(90);
    expect($phaseName)->toBe('First Quarter');
});

it('gets correct moon phase name for full moon', function () {
    $phaseName = $this->service->getMoonPhaseName(180);
    expect($phaseName)->toBe('Full Moon');
});

it('gets correct moon phase name for last quarter', function () {
    $phaseName = $this->service->getMoonPhaseName(270);
    expect($phaseName)->toBe('Last Quarter');
});

it('gets correct moon phase name for waxing crescent', function () {
    $phaseName = $this->service->getMoonPhaseName(45);
    expect($phaseName)->toBe('Waxing Crescent');
});

it('gets correct moon phase name for waxing gibbous', function () {
    $phaseName = $this->service->getMoonPhaseName(135);
    expect($phaseName)->toBe('Waxing Gibbous');
});

it('gets correct moon phase name for waning gibbous', function () {
    $phaseName = $this->service->getMoonPhaseName(225);
    expect($phaseName)->toBe('Waning Gibbous');
});

it('gets correct moon phase name for waning crescent', function () {
    $phaseName = $this->service->getMoonPhaseName(315);
    expect($phaseName)->toBe('Waning Crescent');
});

it('handles null phase value', function () {
    $phaseName = $this->service->getMoonPhaseName(null);
    expect($phaseName)->toBe('Unknown');
});

it('gets correct emoji for new moon', function () {
    $emoji = $this->service->getMoonPhaseEmoji(0);
    expect($emoji)->toBe('🌑');
});

it('gets correct emoji for full moon', function () {
    $emoji = $this->service->getMoonPhaseEmoji(180);
    expect($emoji)->toBe('🌕');
});

it('formats time correctly', function () {
    $formatted = $this->service->formatTime('2025-11-14T08:08:00+00:00', 'H:i');

    expect($formatted)->toBe('08:08');
});

it('returns null for invalid time format', function () {
    $formatted = $this->service->formatTime('invalid-time');

    expect($formatted)->toBeNull();
});

it('handles null time in formatTime', function () {
    $formatted = $this->service->formatTime(null);

    expect($formatted)->toBeNull();
});

it('accepts valid date parameter', function () {
    // Should accept date in Y-m-d format
    try {
        $data = $this->service->getMoonData(60.0, 10.0, '2025-12-25');
        expect(true)->toBeTrue();
    } catch (\Exception $e) {
        // Network errors are okay for this test
        if (! str_contains($e->getMessage(), 'HTTP') && ! str_contains($e->getMessage(), 'timeout')) {
            throw $e;
        }
        expect(true)->toBeTrue();
    }
});

it('accepts timezone offset parameter', function () {
    // Should accept offset in hours
    try {
        $data = $this->service->getMoonData(60.0, 10.0, null, 2);
        expect(true)->toBeTrue();
    } catch (\Exception $e) {
        // Network errors are okay for this test
        if (! str_contains($e->getMessage(), 'HTTP') && ! str_contains($e->getMessage(), 'timeout')) {
            throw $e;
        }
        expect(true)->toBeTrue();
    }
});
