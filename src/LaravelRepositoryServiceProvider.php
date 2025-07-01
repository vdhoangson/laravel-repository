<?php

namespace Vdhoangson\LaravelRepository;

use Illuminate\Support\ServiceProvider;
use Vdhoangson\LaravelRepository\BaseRepository;
use Vdhoangson\LaravelRepository\Contracts\RepositoryInterface;
use Vdhoangson\LaravelRepository\Contracts\BaseCriteriaInterface;
use Vdhoangson\LaravelRepository\Repositories\Criteria\BaseCriteria;

/**
 * Class LaravelRepositoryServiceProvider.
 * */
class LaravelRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $configPath = __DIR__ . '/../config/laravel-repository.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configPath => $this->app->configPath('laravel-repository.php'),
            ], 'laravel-repository-config');
        }

        $this->mergeConfigFrom($configPath, 'laravel-repository');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);
        $this->app->bind(BaseCriteriaInterface::class, BaseCriteria::class);
    }
}
