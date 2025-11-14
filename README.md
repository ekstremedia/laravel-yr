# Laravel Yr Weather

Get weather data from [Yr (MET Norway)](https://api.met.no/) in your Laravel apps. Simple, cached, and ready to use.

## Installation

```bash
composer require ekstremedia/laravel-yr
```

Publish the config:

```bash
php artisan vendor:publish --tag=yr-config
```

Add your details to `.env`:

```env
YR_USER_AGENT="YourApp/1.0 (your.email@example.com)"
```

> **Note:** MET Norway requires you to identify your app with contact info.

## Quick Start

### See it in action

Visit the interactive demo page:

```
http://yourapp.test/yr
```

The demo page features:
- **Live weather display** for any location
- **Location search** - Search by city name (e.g., "Oslo, Norway")
- **Manual coordinates** - Enter latitude/longitude directly
- **Real-time updates** - Weather components update as you change locations

**Search by location:**
```
http://yourapp.test/yr?location=Tokyo,Japan
```

**Use specific coordinates:**
```
http://yourapp.test/yr?latitude=59.9139&longitude=10.7522&location_name=Oslo
```

To disable the demo route, add to your `.env`:
```env
YR_DEMO_ROUTE=false
```

### Get weather by coordinates

```http
GET /api/weather/current?lat=59.9139&lon=10.7522
```

### Get weather by address

```http
GET /api/weather/current?address=Oslo,Norway
```

### Get forecast

```http
GET /api/weather/forecast?lat=59.9139&lon=10.7522
```

### Get sunrise/sunset data

```http
GET /api/weather/sun?lat=59.9139&lon=10.7522
```

### Get moon phase data

```http
GET /api/weather/moon?lat=59.9139&lon=10.7522
```

## Response Example

```json
{
  "success": true,
  "data": {
    "current": {
      "temperature": 8.5,
      "feels_like": 6.2,
      "wind_speed": 3.2,
      "humidity": 65,
      "precipitation_amount": 0.0
    },
    "location": {
      "latitude": 59.9139,
      "longitude": 10.7522,
      "name": "Oslo, Norway"
    }
  }
}
```

## Usage

### Using the Weather Helper (Recommended)

The easiest way to use this package is through the `WeatherHelper` service:

```php
use Ekstremedia\LaravelYr\Services\WeatherHelper;

$helper = app(WeatherHelper::class);

// Get current weather by address
$result = $helper->getWeatherByAddress('Oslo, Norway');
// Returns: ['current' => [...], 'location' => [...]]

// Get current weather by coordinates
$result = $helper->getWeatherByCoordinates(59.9139, 10.7522, altitude: 90);

// Get forecast
$forecast = $helper->getForecastByAddress('Bergen, Norway');
$forecast = $helper->getForecastByCoordinates(60.39, 5.32, complete: true);

// Get sun data (sunrise/sunset)
$sun = $helper->getSunByAddress('Oslo, Norway');
$sun = $helper->getSunByCoordinates(59.9139, 10.7522, date: '2025-12-25', offset: 1);

// Get moon data (phase, rise/set)
$moon = $helper->getMoonByAddress('Bergen, Norway');
$moon = $helper->getMoonByCoordinates(60.39, 5.32, date: '2025-12-25', offset: 1);
```

### Using API Routes

The package automatically registers API routes (fully configurable):

```
GET /api/weather/current?lat=59.9139&lon=10.7522
GET /api/weather/current?address=Oslo,Norway
GET /api/weather/forecast?lat=59.9139&lon=10.7522
```

**Customize API routes** in your `.env`:
```env
# Disable API routes entirely
YR_API_ROUTES=false

# Customize route prefix (default: api/weather)
YR_API_ROUTE_PREFIX=weather

# Customize endpoint names
YR_API_CURRENT_ENDPOINT=now
YR_API_FORECAST_ENDPOINT=predictions
YR_API_SUN_ENDPOINT=sunrise
YR_API_MOON_ENDPOINT=moonphase
```

With the above config, routes become:
- `/weather/now` (current weather)
- `/weather/predictions` (forecast)
- `/weather/sunrise` (sun data)
- `/weather/moonphase` (moon data)

### Using Services Directly

```php
use Ekstremedia\LaravelYr\Services\YrWeatherService;

$weather = app(YrWeatherService::class)->getCurrentWeather(59.9139, 10.7522);

return view('weather', ['weather' => $weather]);
```

### In Blade templates

**Current weather:**
```blade
<x-yr-weather-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
/>
```

**5-Day Forecast:**
```blade
<x-yr-forecast-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo"
    :days="5"
/>
```

**Sunrise/Sunset:**
```blade
<x-yr-sunrise-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
    date="2025-12-25"
    :offset="1"
/>
```

**Moon Phase:**
```blade
<x-yr-moon-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
    date="2025-12-25"
    :offset="1"
/>
```

### In JavaScript

```javascript
const response = await fetch('/api/weather/current?address=Bergen,Norway');
const { data } = await response.json();

console.log(`${data.current.temperature}°C`);
```

## Available Data

### Weather Data

Each weather response includes:

- Temperature (actual and feels-like)
- Wind (speed, direction, gusts)
- Humidity and pressure
- Cloud coverage
- Precipitation
- UV index
- Weather symbol/icon code

### Sun Data

Sunrise/sunset responses include:

- Sunrise and sunset times with azimuth
- Solar noon and solar midnight
- Sun elevation angles
- Daylight duration (hours and minutes)

### Moon Data

Moon phase responses include:

- Moon phase (degrees and name: New Moon, Waxing Crescent, etc.)
- Moon phase emoji visualization
- Moonrise and moonset times with azimuth
- High moon and low moon times with elevation

## Configuration

The config file (`config/yr.php`) has these settings:

```php
return [
    // Required: User agent for MET Norway API
    'user_agent' => env('YR_USER_AGENT', 'YourApp/1.0 (contact@example.com)'),

    // Cache duration in seconds
    'cache_ttl' => env('YR_CACHE_TTL', 3600),

    // Enable/disable demo page at /yr
    'enable_demo_route' => env('YR_DEMO_ROUTE', true),

    // Enable/disable API routes
    'enable_api_routes' => env('YR_API_ROUTES', true),

    // Customize API route prefix (default: 'api/weather')
    'api_route_prefix' => env('YR_API_ROUTE_PREFIX', 'api/weather'),

    // Customize endpoint names
    'api_current_endpoint' => env('YR_API_CURRENT_ENDPOINT', 'current'),
    'api_forecast_endpoint' => env('YR_API_FORECAST_ENDPOINT', 'forecast'),
    'api_sun_endpoint' => env('YR_API_SUN_ENDPOINT', 'sun'),
    'api_moon_endpoint' => env('YR_API_MOON_ENDPOINT', 'moon'),
];
```

Weather data is automatically cached to avoid hitting the API too often.

### How caching works

The package intelligently caches all API requests:

- **Weather data**: Cached by coordinates and parameters. The cache automatically respects the `Expires` header from MET Norway's API, typically updating every hour.
- **Geocoding**: Address lookups are cached for 7 days to minimize requests to the geocoding service.
- **Conditional requests**: The package uses `If-Modified-Since` headers to check if data has changed, reducing bandwidth usage.

All GET endpoints (`/api/weather/current` and `/api/weather/forecast`) benefit from this caching layer, ensuring fast responses while respecting API rate limits.

## Customization

Want to change how the weather cards look? Publish the views:

```bash
php artisan vendor:publish --tag=yr-views
```

Then edit `resources/views/vendor/laravel-yr/components/weather-card.blade.php`.

Want to use local weather icons? Publish the symbols:

```bash
php artisan vendor:publish --tag=yr-symbols
```

This copies 83 weather symbol SVGs to `public/vendor/laravel-yr/symbols`.

## Licensing and Attribution

### Weather Data

All weather data is provided by [The Norwegian Meteorological Institute (MET Norway)](https://www.met.no/) and is licensed under:
- [Norwegian Licence for Open Government Data (NLOD) 2.0](https://data.norge.no/nlod/en/2.0)
- [Creative Commons 4.0 BY International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/)

When using this package, you must provide appropriate credit to MET Norway as the source of the weather data. The package components automatically include the required attribution.

**Required Attribution:**
"Weather data from The Norwegian Meteorological Institute (MET Norway)"

For more details, see [MET Norway's Licensing and Crediting Policy](https://api.met.no/doc/License).

### Weather Icons

The weather symbol SVG files are licensed under the [MIT License](src/resources/symbols/LICENSE).
Copyright (c) 2015-2017 Yr.no.

### Package Code

This Laravel package code is licensed under the MIT License.

## Credits

- Weather data: [The Norwegian Meteorological Institute (MET Norway)](https://www.met.no/)
- Weather icons: [Yr.no](https://www.yr.no/)
- Package developed for easy integration of Norwegian weather data in Laravel applications

## License

MIT License - See LICENSE file for details

Note: This package's MIT license applies only to the package code itself. Weather data and icons have their own licenses as stated above.
