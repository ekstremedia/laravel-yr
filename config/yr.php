<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    |
    | The User-Agent string to identify your application to the MET API.
    | According to MET's terms of service, this should include your app name
    | and contact information.
    |
    | Example: "MyWeatherApp/1.0 (your.email@example.com)"
    |
    */
    'user_agent' => env('YR_USER_AGENT', 'LaravelYr/1.0 (contact@example.com)'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | The time-to-live for cached weather data in seconds.
    | MET recommends not requesting data more frequently than every 10 minutes.
    | Default: 3600 seconds (1 hour)
    |
    */
    'cache_ttl' => env('YR_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Enable Demo Route
    |--------------------------------------------------------------------------
    |
    | Enable the /yr demo route to see an example weather display.
    | Set to true to enable, false to disable.
    |
    */
    'enable_demo_route' => env('YR_DEMO_ROUTE', true),

    /*
    |--------------------------------------------------------------------------
    | Enable API Routes
    |--------------------------------------------------------------------------
    |
    | Enable the API routes for weather data.
    | Set to true to enable, false to disable.
    |
    */
    'enable_api_routes' => env('YR_API_ROUTES', true),

    /*
    |--------------------------------------------------------------------------
    | API Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for the API routes. Default is 'api/weather' which creates
    | routes like /api/weather/current and /api/weather/forecast.
    |
    | Example: 'weather' would create /weather/current and /weather/forecast
    | Example: 'api/yr' would create /api/yr/current and /api/yr/forecast
    |
    */
    'api_route_prefix' => env('YR_API_ROUTE_PREFIX', 'api/weather'),

    /*
    |--------------------------------------------------------------------------
    | API Current Weather Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint name for current weather data.
    | Default: 'current' (creates /api/weather/current)
    |
    */
    'api_current_endpoint' => env('YR_API_CURRENT_ENDPOINT', 'current'),

    /*
    |--------------------------------------------------------------------------
    | API Forecast Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint name for forecast data.
    | Default: 'forecast' (creates /api/weather/forecast)
    |
    */
    'api_forecast_endpoint' => env('YR_API_FORECAST_ENDPOINT', 'forecast'),
];
