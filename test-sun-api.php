<?php

require __DIR__.'/vendor/autoload.php';

// Test the SunService directly
use Ekstremedia\LaravelYr\Services\SunService;
use Illuminate\Support\Facades\Http;

// Mock config
if (! function_exists('config')) {
    function config($key, $default = null)
    {
        $configs = [
            'yr.user_agent' => 'LaravelYr/1.0 (test@example.com)',
            'yr.cache_ttl' => 3600,
        ];

        return $configs[$key] ?? $default;
    }
}

// Mock Cache facade
if (! class_exists('Illuminate\Support\Facades\Cache')) {
    class Cache
    {
        public static function remember($key, $ttl, $callback)
        {
            return $callback();
        }

        public static function put($key, $value, $ttl)
        {
            return true;
        }
    }
    class_alias('Cache', 'Illuminate\Support\Facades\Cache');
}

echo "Testing SunService...\n\n";

$service = new SunService();
$data = $service->getSunData(68.1231, 15.523);

if ($data) {
    echo "✅ Success! Got sun data:\n";
    print_r($data);
} else {
    echo "❌ Failed! Service returned null\n";

    // Try direct HTTP request to debug
    echo "\nTrying direct HTTP request...\n";
    $response = Http::withHeaders([
        'User-Agent' => 'LaravelYr/1.0 (test@example.com)',
    ])->get('https://api.met.no/weatherapi/sunrise/3.0/sun', [
        'lat' => 68.1231,
        'lon' => 15.523,
    ]);

    echo 'Status: '.$response->status()."\n";
    echo 'Body: '.substr($response->body(), 0, 500)."\n";
}
