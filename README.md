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

Visit the demo page to see Sortland, Norway weather:

```
http://yourapp.test/yr
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

### In your routes

```php
use Ekstremedia\LaravelYr\Http\Controllers\WeatherController;

Route::get('weather/current', [WeatherController::class, 'current']);
Route::get('weather/forecast', [WeatherController::class, 'forecast']);
```

### In your code

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

### In JavaScript

```javascript
const response = await fetch('/api/weather/current?address=Bergen,Norway');
const { data } = await response.json();

console.log(`${data.current.temperature}°C`);
```

## Available Data

Each weather response includes:

- Temperature (actual and feels-like)
- Wind (speed, direction, gusts)
- Humidity and pressure
- Cloud coverage
- Precipitation
- UV index
- Weather symbol/icon code

## Configuration

The config file (`config/yr.php`) has two settings:

```php
return [
    'user_agent' => env('YR_USER_AGENT', 'YourApp/1.0 (contact@example.com)'),
    'cache_ttl' => env('YR_CACHE_TTL', 3600), // seconds
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
