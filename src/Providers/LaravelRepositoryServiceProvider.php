<?php

namespace Vdhoangson\LaravelRepository\Providers;

use Illuminate\Support\ServiceProvider;
use Vdhoangson\LaravelRepository\Repositories\BaseRepository;
use Vdhoangson\LaravelRepository\Repositories\Criteria\BaseCriteria;
use Vdhoangson\LaravelRepository\Repositories\Interfaces\BaseInterface;
use Vdhoangson\LaravelRepository\Repositories\Criteria\Interfaces\BaseCriteriaInterface;

/**
 * Class LaravelRepositoryServiceProvider.
 * */
class LaravelRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $path = __DIR__ . '/../../resources/config/laravel-repository.php';

        $this->publishes([
            $path => $this->app->configPath('laravel-repository.php'),
        ]);

        $this->mergeConfigFrom($path, 'laravel-repository');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BaseInterface::class, BaseRepository::class);
        $this->app->bind(BaseCriteriaInterface::class, BaseCriteria::class);
    }
}
