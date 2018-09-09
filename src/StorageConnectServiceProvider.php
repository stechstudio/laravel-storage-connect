<?php

namespace STS\StorageConnect;

use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Dropbox\DropboxExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use STS\StorageConnect\Providers\DropboxProvider;
use STS\StorageConnect\Providers\GoogleProvider;

class StorageConnectServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/storage-connect.php' => config_path('storage-connect.php'),
            ], 'config');

            /*
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/skeleton'),
            ], 'views');
            */
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/storage-connect.php', 'storage-connect');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->app->singleton(StorageConnectManager::class, function($app) {
            return new StorageConnectManager($app);
        });
        $this->app->alias(StorageConnectManager::class, 'sts.storage-connect');

        $this->app->register(
            \SocialiteProviders\Manager\ServiceProvider::class
        );

//        $this->app['events']->listen(SocialiteWasCalled::class, function(SocialiteWasCalled $event) {
//            $event->extendSocialite('dropbox', DropboxProvider::class);
//            $event->extendSocialite('google', GoogleProvider::class);
//        });
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
