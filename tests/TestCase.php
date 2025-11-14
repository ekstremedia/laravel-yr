<?php

namespace YourVendor\LaravelYr\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use YourVendor\LaravelYr\YrServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            YrServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('yr.user_agent', 'LaravelYrTestSuite/1.0 (test@example.com)');
        config()->set('yr.cache_ttl', 3600);
        config()->set('cache.default', 'array');
    }
}
