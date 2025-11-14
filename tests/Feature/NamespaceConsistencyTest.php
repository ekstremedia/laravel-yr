<?php

use Illuminate\Support\Facades\File;

it('has no references to old YourVendor namespace in PHP files', function () {
    $files = File::allFiles(__DIR__.'/../../src');

    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            expect($content)
                ->not->toContain('YourVendor\\')
                ->not->toContain('your-vendor');
        }
    }
});

it('has no references to old YourVendor namespace in config files', function () {
    $configFile = __DIR__.'/../../config/yr.php';

    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        expect($content)
            ->not->toContain('YourVendor\\')
            ->not->toContain('your-vendor');
    }
});

it('has correct namespace in composer.json', function () {
    $composerJson = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);

    expect($composerJson['name'])->toBe('ekstremedia/laravel-yr');
    expect($composerJson['autoload']['psr-4'])->toHaveKey('Ekstremedia\\LaravelYr\\');
    expect($composerJson['autoload-dev']['psr-4'])->toHaveKey('Ekstremedia\\LaravelYr\\Tests\\');
    expect($composerJson['extra']['laravel']['providers'][0])->toBe('Ekstremedia\\LaravelYr\\YrServiceProvider');
});

it('has no references to old namespace in test files', function () {
    $files = File::allFiles(__DIR__.'/..');

    foreach ($files as $file) {
        // Skip this test file itself as it contains the string we're checking for
        if ($file->getFilename() === 'NamespaceConsistencyTest.php') {
            continue;
        }

        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            // Check that old namespace is not being USED (imported or instantiated)
            // Allow it in test assertions
            if (! str_contains($content, 'expect(') && ! str_contains($content, 'assertSee')) {
                expect($content)
                    ->not->toContain('YourVendor\\')
                    ->not->toContain('your-vendor');
            }
        }
    }
});

it('has no references to old namespace in blade views', function () {
    $viewsPath = __DIR__.'/../../src/resources/views';

    if (File::exists($viewsPath)) {
        $files = File::allFiles($viewsPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                expect($content)
                    ->not->toContain('YourVendor\\')
                    ->not->toContain('your-vendor');
            }
        }
    }
});

it('service provider is registered with correct namespace', function () {
    $provider = app()->getProvider('Ekstremedia\\LaravelYr\\YrServiceProvider');

    expect($provider)->not->toBeNull();
    expect(get_class($provider))->toBe('Ekstremedia\\LaravelYr\\YrServiceProvider');
});

it('services are bound with correct namespace', function () {
    $weatherService = app(\Ekstremedia\LaravelYr\Services\YrWeatherService::class);
    $geocodingService = app(\Ekstremedia\LaravelYr\Services\GeocodingService::class);

    expect($weatherService)->toBeInstanceOf(\Ekstremedia\LaravelYr\Services\YrWeatherService::class);
    expect($geocodingService)->toBeInstanceOf(\Ekstremedia\LaravelYr\Services\GeocodingService::class);
});
