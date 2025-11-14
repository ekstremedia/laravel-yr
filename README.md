# Laravel Yr Weather

Get weather data from [Yr (MET Norway)](https://api.met.no/) in your Laravel apps. Simple, cached, and ready to use.

## Installation

```bash
composer require your-vendor/laravel-yr
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
use YourVendor\LaravelYr\Http\Controllers\WeatherController;

Route::get('weather/current', [WeatherController::class, 'current']);
Route::get('weather/forecast', [WeatherController::class, 'forecast']);
```

### In your code

```php
use YourVendor\LaravelYr\Services\YrWeatherService;

$weather = app(YrWeatherService::class)->getCurrentWeather(59.9139, 10.7522);

return view('weather', ['weather' => $weather]);
```

### In Blade templates

```blade
<x-yr-weather-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
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

## Norwegian Cities

Some coordinates to get you started:

```php
Oslo      → 59.9139, 10.7522
Bergen    → 60.3913, 5.3221
Trondheim → 63.4305, 10.3951
Stavanger → 58.9700, 5.7331
Tromsø    → 69.6492, 18.9553
```

## Customization

Want to change how the weather cards look? Publish the views:

```bash
php artisan vendor:publish --tag=yr-views
```

Then edit `resources/views/vendor/laravel-yr/components/weather-card.blade.php`.

## Credits

Weather data from [MET Norway](https://api.met.no/). Thanks for the awesome API!

## License

MIT
