# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package (`ekstremedia/laravel-yr`) that integrates weather data from MET Norway's API (api.met.no). The package provides:
- RESTful API endpoints for current weather and forecasts
- Geocoding support (address to coordinates conversion)
- Blade components for displaying weather data
- An interactive demo page
- Full compliance with MET Norway's API requirements

**Key Compliance Requirement:** All API requests to MET Norway MUST include a proper User-Agent header with contact information. Coordinates must be truncated to max 4 decimals per MET API TOS.

## Development Commands

```bash
# Run all tests (Pest)
composer test
# or
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/DemoRouteTest.php

# Run tests with coverage
composer test:coverage

# Run tests with CI formatting
./vendor/bin/pest --ci

# Code formatting (Laravel Pint)
composer format          # Fix code style issues
composer format:test     # Check code style without fixing

# Install dependencies
composer install
```

## Architecture

### Service Layer
The package uses three main services:

**WeatherHelper** (`src/Services/WeatherHelper.php`) - **Recommended for developers**
- High-level service that provides clean API for getting weather data
- Methods: `getWeatherByAddress()`, `getWeatherByCoordinates()`, `getForecastByAddress()`, `getForecastByCoordinates()`
- Handles all validation (lat/lon ranges, altitude limits, address format)
- Automatically uses GeocodingService for address lookups
- Returns structured arrays: `['current' => [...], 'location' => [...]]`
- Throws `InvalidArgumentException` for validation errors
- Returns `null` if weather service or geocoding fails
- **This is the recommended way to use the package in PHP code**

**YrWeatherService** (`src/Services/YrWeatherService.php`)
- Core service for MET Norway API integration
- Uses Guzzle HTTP client with automatic User-Agent injection
- Implements intelligent caching with `Expires` header and `If-Modified-Since` support
- Truncates coordinates to 4 decimals (MET API requirement)
- Supports both `/compact` (default) and `/complete` endpoints
- Provides methods: `getForecast()`, `getCurrentWeather()`
- Calculates "feels-like" temperature using wind chill and heat index formulas

**GeocodingService** (`src/Services/GeocodingService.php`)
- Uses OpenStreetMap Nominatim API for geocoding
- Forward geocoding: address → coordinates
- Reverse geocoding: coordinates → address
- 7-day caching for geocoding results
- Truncates coordinates to 4 decimals before returning

All services are registered as singletons in `YrServiceProvider.php` and injected via constructor with config values.

### Controller Layer & API Routes
**WeatherController** (`src/Http/Controllers/WeatherController.php`)
- Two endpoints: `current()` and `forecast()`
- Accepts either coordinates (`lat`/`lon`) OR address
- If address provided, uses GeocodingService to resolve coordinates
- Optional `altitude` parameter (validated -500 to 9000m)
- Returns JSON with `success` and `data` structure
- Comprehensive validation for lat/lon ranges

**API Routes** (`routes/api.php`)
- Automatically registered by package (no manual setup needed)
- Routes: `/api/weather/current` and `/api/weather/forecast` (defaults, fully configurable)
- Named routes: `yr.api.current` and `yr.api.forecast`
- **Fully Configurable**:
  - Enable/disable: `YR_API_ROUTES=false`
  - Custom prefix: `YR_API_ROUTE_PREFIX=weather` (changes `/api/weather/*` to `/weather/*`)
  - Custom endpoints: `YR_API_CURRENT_ENDPOINT=now` and `YR_API_FORECAST_ENDPOINT=predictions`
  - Example: With `YR_API_ROUTE_PREFIX=api/yr`, `YR_API_CURRENT_ENDPOINT=now`, routes become `/api/yr/now`
- Loaded conditionally in `YrServiceProvider::boot()`
- Routes use config values, so changes require cache clearing in production

### View Layer
**Blade Components:**
- `WeatherCard` - Current weather display with glassmorphic dark theme
- `ForecastCard` - 5-day forecast with expandable hourly details (uses Alpine.js)

Both components:
- Accept `latitude`, `longitude`, `location` props
- Use scoped CSS (styles embedded in component files)
- Mobile-responsive with multiple breakpoints (768px, 640px, 480px)
- Temperature-based color coding (warm/cold gradients)

**Demo Page** (`routes/web.php` → `src/resources/views/demo.blade.php`)
- Route: `/yr` (configurable via `YR_DEMO_ROUTE` env)
- Interactive form with two modes: location search or manual coordinates
- Uses Alpine.js for UI interactivity
- Query parameters: `?location=Oslo,Norway` or `?latitude=X&longitude=Y&location_name=Name`
- Passes coordinates to both WeatherCard and ForecastCard components

### Configuration
**Config file:** `config/yr.php`
- `user_agent` - Required by MET Norway (must include contact info)
- `cache_ttl` - Default cache duration (default: 3600s)
- `enable_demo_route` - Toggle demo page on/off (default: true)
- `enable_api_routes` - Toggle API routes on/off (default: true)
- `api_route_prefix` - Route prefix for API endpoints (default: `api/weather`)
- `api_current_endpoint` - Endpoint name for current weather (default: `current`)
- `api_forecast_endpoint` - Endpoint name for forecast (default: `forecast`)

**Environment variables:**
```env
YR_USER_AGENT="YourApp/1.0 (your.email@example.com)"
YR_CACHE_TTL=3600
YR_DEMO_ROUTE=true
YR_API_ROUTES=true
YR_API_ROUTE_PREFIX=api/weather
YR_API_CURRENT_ENDPOINT=current
YR_API_FORECAST_ENDPOINT=forecast
```

**Example custom configuration:**
```env
# Use /weather/now instead of /api/weather/current
YR_API_ROUTE_PREFIX=weather
YR_API_CURRENT_ENDPOINT=now
YR_API_FORECAST_ENDPOINT=predictions
```

### Service Provider
**YrServiceProvider** (`src/YrServiceProvider.php`)
- Registers services as singletons with config injection
- Publishes config, views, and weather symbols
- Registers Blade components (`x-yr-weather-card`, `x-yr-forecast-card`)
- Conditionally loads demo route based on config

## Testing Strategy

**Test Suite:** Pest PHP (59 tests, 175 assertions)

Tests are organized into:
- `tests/Unit/` - Service unit tests (no external API calls)
- `tests/Feature/` - HTTP endpoint tests, Blade component rendering, route configuration

**Key Test Files:**
- `YrWeatherServiceTest.php` - Symbol URLs, calculations, coordinate truncation
- `GeocodingServiceTest.php` - Instantiation, user agent, coordinate truncation
- `WeatherHelperTest.php` - Validation for all methods, error handling, address/coordinate inputs
- `WeatherControllerTest.php` - Validation, endpoints, error handling
- `ApiRoutesConfigTest.php` - Tests that API routes can be enabled/disabled via config
- `BladeComponentRenderingTest.php` - Component rendering, namespace checks
- `DemoRouteTest.php` - Location search, coordinates, form preservation, error handling
- `NamespaceConsistencyTest.php` - Ensures no old namespace references (important for package integrity)
- `ComponentAttributionTest.php` - Verifies MET Norway licensing attribution

**Important:** Tests use Orchestra Testbench and don't make real API calls. They verify logic, validation, and integration without external dependencies.

## Caching Strategy

The package implements multi-layer caching:

1. **Weather Data Cache:**
   - Cache key format: `yr_weather_{lat}_{lon}_{altitude}_{endpoint}`
   - Respects `Expires` header from MET API
   - Uses `If-Modified-Since` for conditional requests (304 responses)
   - Stores `Last-Modified` metadata separately

2. **Geocoding Cache:**
   - 7-day TTL for address lookups
   - Prevents excessive requests to Nominatim

3. **View Cache:**
   - Laravel's standard Blade compilation cache
   - Clear with `php artisan view:clear` after Blade changes

## MET Norway API Compliance

Critical requirements implemented:
- User-Agent header with contact info (configurable)
- Coordinate precision: max 4 decimals
- Respects cache headers (`Expires`, `Last-Modified`)
- Minimum cache time: 60 seconds (package default: 1 hour)
- Uses `/compact` endpoint by default (smaller payload)
- Proper attribution in components

## Weather Symbol Icons

Weather symbols are SVG files licensed under MIT from Yr.no. The package can:
- Use remote URLs from api.met.no (default)
- Use local symbols if published via `php artisan vendor:publish --tag=yr-symbols`

Local symbols path: `public/vendor/laravel-yr/symbols/`

The `YrWeatherService::getSymbolUrl()` method checks if local symbols exist before falling back to remote URLs.

## Recommended Usage Pattern

**For developers writing PHP code**, use `WeatherHelper`:
```php
use Ekstremedia\LaravelYr\Services\WeatherHelper;

$helper = app(WeatherHelper::class);

// By address
$result = $helper->getWeatherByAddress('Oslo, Norway');
if ($result) {
    $temperature = $result['current']['temperature'];
    $location = $result['location']['name'];
}

// By coordinates
$result = $helper->getWeatherByCoordinates(59.9139, 10.7522, altitude: 90);
if ($result) {
    // Handle weather data
}

// Forecast
$forecast = $helper->getForecastByAddress('Bergen, Norway');
```

**For JavaScript/API consumers**, use the API routes:
```javascript
const response = await fetch('/api/weather/current?address=Oslo,Norway');
const { data } = await response.json();
console.log(data.current.temperature);
```

**For Blade templates**, use components:
```blade
<x-yr-weather-card :latitude="59.9139" :longitude="10.7522" location="Oslo" />
```

## Common Pitfalls

1. **Missing User-Agent:** The package will fail if `YR_USER_AGENT` is not set. Always configure this in `.env`.

2. **View Cache Issues:** After modifying Blade components, run `php artisan view:clear` in the host Laravel app, not the package directory.

3. **Coordinate Precision:** Always use the services' methods which auto-truncate coordinates. Manual API calls must truncate to 4 decimals.

4. **Test Isolation:** Tests use Orchestra Testbench which creates a fresh Laravel environment. Real-world cache issues may not appear in tests.

5. **Geocoding Service:** Uses OpenStreetMap Nominatim which has usage limits. The package caches results but avoid excessive lookups during development.

## Package Publishing

This package auto-discovers via Laravel's package discovery. Users can publish:
- Config: `php artisan vendor:publish --tag=yr-config`
- Views: `php artisan vendor:publish --tag=yr-views`
- Symbols: `php artisan vendor:publish --tag=yr-symbols`

## Mobile Responsiveness

Both Blade components have comprehensive responsive styles:
- Breakpoints: 768px (tablet), 640px/480px (mobile)
- Forecast card switches to vertical layout on mobile
- Form inputs stack on small screens
- Font sizes and spacing scale down appropriately

When modifying styles, test at all three breakpoints.

## Alpine.js Integration

The ForecastCard and Demo page use Alpine.js for interactivity:
- ForecastCard: Expandable daily forecasts (`x-data`, `x-show`, `x-collapse`)
- Demo page: Toggle between location/coordinate search modes

Alpine.js is loaded via CDN in both component files. If modifying, ensure Alpine directives are properly structured.
