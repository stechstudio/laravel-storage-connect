<?php

namespace STS\StorageConnect;

use Illuminate\Support\ServiceProvider;
use STS\StorageConnect\Subscribers\LogsActivity;

class StorageConnectServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/storage-connect.php' => config_path('storage-connect.php')], 'config');
        }

        if ($this->app['config']->get('storage-connect.log_activity') == true) {
            $this->app['events']->subscribe(LogsActivity::class);
        }

        $this->app[StorageConnectManager::class]->register('dropbox', \STS\StorageConnect\Drivers\Dropbox\Adapter::class, \STS\StorageConnect\Drivers\Dropbox\Provider::class);
        $this->app[StorageConnectManager::class]->register('google', \STS\StorageConnect\Drivers\Google\Adapter::class, \STS\StorageConnect\Drivers\Google\Provider::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/storage-connect.php', 'storage-connect');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->app->singleton(StorageConnectManager::class, function ($app) {
            return new StorageConnectManager($app);
        });
        $this->app->alias(StorageConnectManager::class, 'sts.storage-connect');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['sts.storage-connect', StorageConnectManager::class];
    }
}
