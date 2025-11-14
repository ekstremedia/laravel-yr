# Testing Strategy

## Why Tests Didn't Catch the Namespace Issue

The cached view compilation issue wasn't caught by the original tests for several reasons:

### 1. **Test Environment Isolation**
- Orchestra Testbench creates a fresh Laravel environment for each test run
- View caches use `array` driver in tests, which clears between tests
- Tests load classes directly from source code, not through cached discovery

### 2. **Real Environment Differences**
- Development/production environments use persistent file-based caches
- Compiled Blade views are cached in `storage/framework/views/`
- Service provider discovery is cached in `bootstrap/cache/`
- When namespaces change, these caches contain old compiled code

### 3. **What Actually Failed**
The error occurred because:
- **Cached compiled Blade views** contained old namespace references
- Laravel's view compiler had cached: `new YourVendor\LaravelYr\View\Components\WeatherCard`
- Clearing caches (`php artisan view:clear`) resolved the issue

## New Tests to Prevent This

We've added comprehensive tests to catch namespace issues:

### 1. **NamespaceConsistencyTest.php**
Tests that verify:
- ✅ No `YourVendor` references in PHP source files
- ✅ No `YourVendor` references in Blade views
- ✅ Correct namespace in `composer.json`
- ✅ Service provider registered with correct namespace
- ✅ Services bound with correct namespace classes

### 2. **BladeComponentRenderingTest.php**
Tests that verify:
- ✅ Blade components render without errors via `<x-yr-*>` syntax
- ✅ Rendered output contains no old namespace references
- ✅ Components can be instantiated with correct namespace
- ✅ Demo route renders without errors

### 3. **DemoRouteTest.php**
Tests that verify:
- ✅ Demo route renders with default location (Sortland, Norway)
- ✅ Demo route accepts manual coordinates via query parameters
- ✅ Demo route handles location search parameter with geocoding
- ✅ Demo route displays error when location is not found
- ✅ Demo route includes all search form elements
- ✅ Demo route uses Alpine.js for interactive toggle
- ✅ Demo route renders weather components with dynamic coordinates
- ✅ Demo route preserves location search input after submission
- ✅ Demo route preserves coordinate inputs after submission

## Test Coverage

Total: **41 tests** with **143 assertions**

### Breakdown:
- **Namespace Consistency**: 7 tests
- **Blade Component Rendering**: 5 tests
- **Component Attribution**: 2 tests
- **Demo Route Functionality**: 9 tests
- **Weather Controller API**: 8 tests
- **Geocoding Service**: 3 tests
- **Weather Service**: 7 tests

## Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run with CI formatting
./vendor/bin/pest --ci

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test file
./vendor/bin/pest tests/Feature/NamespaceConsistencyTest.php
```

## Cache Management for Deployments

When deploying or updating the package, always clear caches:

```bash
# In host Laravel application
php artisan view:clear
php artisan config:clear
php artisan route:clear
composer dump-autoload
```

## Preventing Future Issues

### For Package Development:
1. Always run tests after namespace changes
2. Clear test caches: `rm -rf vendor/orchestra/testbench-core/laravel/storage/framework/views/*`
3. Test in a real Laravel app, not just Orchestra Testbench

### For Package Users:
1. After updating the package, clear all caches
2. Remove `bootstrap/cache/*.php` files
3. Run `composer dump-autoload`

## CI/CD Pipeline

The package includes GitHub Actions workflow that:
1. Tests against PHP 8.2 and 8.3
2. Tests against Laravel 10, 11, and 12
3. Runs all tests including namespace consistency checks
4. Ensures no old namespace references exist before merge

## What We Fixed

1. **composer.json**: Updated package name and namespaces
2. **All PHP files**: Changed from `YourVendor\LaravelYr` to `Ekstremedia\LaravelYr`
3. **phpunit.xml**: Updated test suite name
4. **PACKAGE_SUMMARY.md**: Updated code examples
5. **Added comprehensive tests**: To prevent future namespace issues

## Lessons Learned

1. **Caching is powerful but dangerous**: Always clear caches after major changes
2. **Test in real environments**: Orchestra Testbench is great but doesn't catch everything
3. **Namespace changes are risky**: Require thorough testing and documentation
4. **Cache-related bugs are silent**: They only appear in persistent environments
5. **Comprehensive tests are essential**: Especially for package metadata and registration
