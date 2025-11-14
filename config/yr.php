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
];
