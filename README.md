# Laravel Yr Weather Package

[![Tests](https://github.com/your-vendor/laravel-yr/workflows/Tests/badge.svg)](https://github.com/your-vendor/laravel-yr/actions)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3-blue)](https://php.net)

A comprehensive Laravel package for integrating weather data from [Yr (MET Norway API)](https://api.met.no/) into your Laravel applications. Fully compliant with MET.no API specifications and guidelines.

## Features

- 🌤️ **Comprehensive Weather Data** - Temperature, wind, humidity, UV index, precipitation, and more
- 📍 **Flexible Location Input** - Query by coordinates OR address (geocoding included)
- ⚡ **Smart Caching** - Respects MET.no Expires headers and configurable TTL
- 🎨 **Beautiful Blade Components** - Ready-to-use weather cards with styling
- 🔌 **RESTful JSON API** - Perfect for Vue, React, or any frontend framework
- 🏔️ **Altitude Support** - Accurate temperature readings for mountainous regions
- 🧪 **Full Test Coverage** - Comprehensive Pest test suite
- 🚀 **CI/CD Ready** - GitHub Actions workflow included
- 📦 **Laravel 10, 11, and 12** - Multi-version support

## Installation

Install via Composer:

```bash
composer require your-vendor/laravel-yr
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=yr-config
```

Optionally publish views for customization:

```bash
php artisan vendor:publish --tag=yr-views
```

## Configuration

Add to your `.env` file:

```env
YR_USER_AGENT="YourApp/1.0 (your.email@example.com)"
YR_CACHE_TTL=3600
```

⚠️ **Important**: MET.no requires a unique User-Agent string with contact information. Requests without proper identification will receive a 403 Forbidden error.

## Usage

### API Endpoints

The package provides flexible API endpoints that accept either coordinates or addresses:

#### Get Current Weather

```http
# By coordinates
GET /api/weather/current?lat=59.9139&lon=10.7522

# By address
GET /api/weather/current?address=Oslo,Norway

# With altitude for better accuracy
GET /api/weather/current?lat=59.9139&lon=10.7522&altitude=90
```

#### Get Forecast

```http
# Compact forecast (default)
GET /api/weather/forecast?lat=59.9139&lon=10.7522

# Complete forecast with all data
GET /api/weather/forecast?address=Bergen,Norway&complete=1
```

#### Example JSON Response

```json
{
  "success": true,
  "data": {
    "current": {
      "time": "2025-11-14T12:00:00Z",
      "temperature": 8.5,
      "feels_like": 6.2,
      "wind_speed": 3.2,
      "wind_direction": 180,
      "wind_gust": 5.1,
      "humidity": 65,
      "pressure": 1013.2,
      "cloud_coverage": 45,
      "dew_point": 2.1,
      "precipitation_amount": 0.0,
      "uv_index": 1.2,
      "symbol_code": "partly_cloudy_day"
    },
    "location": {
      "latitude": 59.9139,
      "longitude": 10.7522,
      "altitude": 90,
      "name": "Oslo, Norway"
    }
  }
}
```

### Using the Blade Component

Display weather anywhere in your Blade templates:

```blade
<x-yr-weather-card
    :latitude="59.9139"
    :longitude="10.7522"
    location="Oslo, Norway"
/>
```

The component includes beautiful styling with gradients, weather icons, and responsive layout.

### Using the Services Directly

#### YrWeatherService

```php
use YourVendor\LaravelYr\Services\YrWeatherService;

public function index(YrWeatherService $weatherService)
{
    // Get current weather
    $current = $weatherService->getCurrentWeather(
        latitude: 59.9139,
        longitude: 10.7522,
        altitude: 90  // Optional but recommended
    );

    // Get full forecast
    $forecast = $weatherService->getForecast(
        latitude: 59.9139,
        longitude: 10.7522,
        altitude: 90,
        complete: false  // Use true for complete dataset
    );

    // Get weather icon URL
    $iconUrl = $weatherService->getSymbolUrl('clearsky_day');

    return view('weather', compact('current', 'forecast', 'iconUrl'));
}
```

#### GeocodingService

```php
use YourVendor\LaravelYr\Services\GeocodingService;

public function search(GeocodingService $geocoding)
{
    // Geocode an address
    $location = $geocoding->geocode('Oslo, Norway');
    // Returns: ['latitude' => 59.9139, 'longitude' => 10.7522, 'display_name' => '...']

    // Reverse geocode coordinates
    $address = $geocoding->reverseGeocode(59.9139, 10.7522);
    // Returns: ['display_name' => 'Oslo, Norway', 'address' => [...]]
}
```

### Frontend Integration Example

```javascript
async function getWeather(address) {
    const response = await fetch(`/api/weather/current?address=${encodeURIComponent(address)}`);
    const data = await response.json();

    if (data.success) {
        console.log(`Temperature: ${data.data.current.temperature}°C`);
        console.log(`Location: ${data.data.location.name}`);
    }
}

// Usage
getWeather('Bergen, Norway');
```

## Available Weather Data

The package provides comprehensive weather information:

### Current Conditions
- **Temperature**: Air temperature, feels-like temperature, dew point
- **Wind**: Speed, direction (0° = north), gust speed
- **Atmospheric**: Humidity, pressure, cloud coverage, fog
- **Precipitation**: Amount, min/max predictions
- **UV Index**: Clear-sky UV radiation index
- **Weather Symbol**: Icon code for current conditions

### Forecast Data
- 9-day forecast with hourly granularity
- Next 1h, 6h, and 12h summaries
- Probability data (when using `complete` mode)
- Automatic cache management with Expires headers

## Testing

The package includes a comprehensive Pest test suite:

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Check code style
composer format:test

# Fix code style
composer format
```

## Development

### Running Tests Locally

```bash
# Install dependencies
composer install

# Run the test suite
vendor/bin/pest

# Check code formatting
vendor/bin/pint --test
```

### GitHub Actions

The package includes a complete CI/CD pipeline that runs on push and pull requests:
- Pest tests across PHP 8.1, 8.2, 8.3
- Laravel 10, 11, and 12 compatibility matrix
- Pint code style checks

## Norwegian Cities Coordinates

Useful coordinates for testing:

| City | Latitude | Longitude |
|------|----------|-----------|
| Oslo | 59.9139 | 10.7522 |
| Bergen | 60.3913 | 5.3221 |
| Trondheim | 63.4305 | 10.3951 |
| Stavanger | 58.9700 | 5.7331 |
| Tromsø | 69.6492 | 18.9553 |

## MET.no API Compliance

This package fully complies with MET.no's requirements:

- ✅ Unique User-Agent with contact information
- ✅ Respects cache headers (Expires, Last-Modified)
- ✅ Automatic caching to prevent excessive requests
- ✅ Uses recommended `/compact` endpoint by default
- ✅ Supports altitude parameter for accurate temperatures
- ✅ Proper error handling and reporting

## Advanced Usage

### Custom Routes

Add custom routes to `routes/api.php`:

```php
use YourVendor\LaravelYr\Http\Controllers\WeatherController;

Route::prefix('v1/weather')->group(function () {
    Route::get('current', [WeatherController::class, 'current']);
    Route::get('forecast', [WeatherController::class, 'forecast']);
});
```

### Custom Caching Strategy

Override the cache TTL programmatically:

```php
config(['yr.cache_ttl' => 1800]); // 30 minutes

$weather = app(YrWeatherService::class)
    ->getCurrentWeather(59.9139, 10.7522);
```

### Error Handling

```php
try {
    $weather = $weatherService->getCurrentWeather(59.9139, 10.7522);

    if ($weather === null) {
        // Handle API unavailability
        return response()->json(['error' => 'Weather service unavailable'], 503);
    }
} catch (\Exception $e) {
    report($e);
    // Handle exception
}
```

## Changelog

### Version 1.0.0
- Initial release
- Weather data from MET Norway API
- Geocoding support
- Altitude parameter support
- Comprehensive test coverage
- GitHub Actions CI/CD
- Laravel 10, 11, 12 support

## Contributing

Contributions are welcome! Please submit pull requests with:
- Tests for new features
- Updated documentation
- Code formatted with Pint

## License

MIT License - see LICENSE file for details

## Credits

- Weather data provided by [MET Norway](https://api.met.no/)
- Geocoding via [OpenStreetMap Nominatim](https://nominatim.org/)
- Weather icons from [MET Norway Weather Icons](https://github.com/metno/weathericons)

## Support

- GitHub Issues: [Report bugs or request features](https://github.com/your-vendor/laravel-yr/issues)
- Email: your.email@example.com

---

Made with ❤️ for the Laravel community
