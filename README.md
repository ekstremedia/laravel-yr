# Laravel Yr Weather Package

A lightweight Laravel package for integrating weather data from Yr (api.met.no) into your Laravel applications.

## Features

- Simple, intuitive API for fetching weather data
- Automatic caching to respect MET.no API guidelines
- Beautiful Blade component for displaying weather
- Example controller for API endpoints
- Configurable user agent and cache settings
- Supports current weather and forecasts

## Installation

Install the package via Composer:

```bash
composer require your-vendor/laravel-yr
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=yr-config
```

Optionally, publish the views if you want to customize them:

```bash
php artisan vendor:publish --tag=yr-views
```

## Configuration

Update your `.env` file with your application details:

```env
YR_USER_AGENT="YourApp/1.0 (your.email@example.com)"
YR_CACHE_TTL=3600
```

According to MET.no's terms of service, the User-Agent should identify your application and include contact information.

## Usage

### Using the Service Directly

Inject the `YrWeatherService` into your controllers or services:

```php
use YourVendor\LaravelYr\Services\YrWeatherService;

class YourController extends Controller
{
    public function show(YrWeatherService $weatherService)
    {
        // Get current weather
        $current = $weatherService->getCurrentWeather(59.9139, 10.7522);

        // Get full forecast
        $forecast = $weatherService->getForecast(59.9139, 10.7522);

        return view('your-view', compact('current', 'forecast'));
    }
}
```

### Using the Blade Component

Display weather in your Blade views using the weather card component:

```blade
<x-yr-weather-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
/>
```

The component includes built-in styling and displays:
- Location name
- Current temperature
- Weather icon
- Wind speed
- Humidity
- Precipitation
- Last update time

### Using the Example Controller

Add routes to your `routes/api.php` or `routes/web.php`:

```php
use YourVendor\LaravelYr\Http\Controllers\WeatherController;

Route::get('/weather/current', [WeatherController::class, 'current']);
Route::get('/weather/forecast', [WeatherController::class, 'forecast']);
```

Then access the endpoints:

```
GET /weather/current?lat=59.9139&lon=10.7522
GET /weather/forecast?lat=59.9139&lon=10.7522
```

### Example Response

```json
{
    "data": {
        "time": "2025-11-14T12:00:00Z",
        "temperature": 8.5,
        "wind_speed": 3.2,
        "wind_direction": 180,
        "humidity": 65,
        "pressure": 1013.2,
        "precipitation": 0.0,
        "symbol_code": "clearsky_day"
    },
    "coordinates": {
        "latitude": "59.9139",
        "longitude": "10.7522"
    }
}
```

## API Reference

### YrWeatherService

#### `getForecast(float $latitude, float $longitude): ?array`

Get the full weather forecast for given coordinates. Returns an array with:
- `timeseries`: Array of weather data points
- `updated_at`: Last update timestamp

#### `getCurrentWeather(float $latitude, float $longitude): ?array`

Get current weather conditions. Returns the first entry from the forecast timeseries.

#### `getSymbolUrl(string $symbolCode): string`

Get the URL for a weather symbol icon from MET.no.

## Coordinates Examples

Here are some coordinates for Norwegian cities:

- Oslo: `59.9139, 10.7522`
- Bergen: `60.3913, 5.3221`
- Trondheim: `63.4305, 10.3951`
- Stavanger: `58.9700, 5.7331`
- Tromsø: `69.6492, 18.9553`

## Caching

The package automatically caches weather data to comply with MET.no's API guidelines. The default cache TTL is 1 hour (3600 seconds), but you can configure this in the `config/yr.php` file or via the `YR_CACHE_TTL` environment variable.

## Credits

Weather data provided by [MET Norway](https://api.met.no/).

## License

MIT License
# laravel-yr
