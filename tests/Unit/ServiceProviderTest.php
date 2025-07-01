<?php

namespace Vdhoangson\LaravelRepository\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Vdhoangson\LaravelRepository\LaravelRepositoryServiceProvider;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelRepositoryServiceProvider::class];
    }

    public function test_service_provider_is_loaded()
    {
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(LaravelRepositoryServiceProvider::class, $providers);
    }

    public function test_config_is_published()
    {
        $this->assertTrue($this->app->bound('config'));
    }
}
