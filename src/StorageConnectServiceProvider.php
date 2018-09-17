<?php

namespace STS\StorageConnect;

use Illuminate\Support\ServiceProvider;
use STS\StorageConnect\Events\CloudStorageDisabled;
use STS\StorageConnect\Events\CloudStorageEnabled;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\CloudStorageSetup;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadSucceeded;

class StorageConnectServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/storage-connect.php' => config_path('storage-connect.php')], 'config');
        }

        if ($this->app['config']->get('storage-connect.log_activity') == true) {
            $this->listenAndLog();
        }
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

    /**
     * Listen to our own events and log activity
     */
    protected function listenAndLog()
    {
        $this->app['events']->listen(CloudStorageSetup::class, function (CloudStorageSetup $event) {
            $this->app['log']->info("New cloud storage connection", [
                'storage' => $event->storage->id,
                'driver'  => $event->storage->driver,
                'owner'   => $event->storage->owner_description
            ]);
        });

        $this->app['events']->listen(UploadSucceeded::class, function (UploadSucceeded $event) {
            $this->app['log']->info("File uploaded to cloud storage", [
                'source'      => $event->sourcePath,
                'destination' => $event->destinationPath,
                'storage'     => $event->storage->id,
                'driver'      => $event->storage->driver,
                'owner'       => $event->storage->owner_description,
            ]);
        });

        $this->app['events']->listen(UploadRetrying::class, function (UploadRetrying $event) {
            $this->app['log']->warning($event->message, [
                'source'  => $event->sourcePath,
                'storage' => $event->storage->id,
                'driver'  => $event->storage->driver,
                'owner'   => $event->storage->owner_description,
            ]);
        });

        $this->app['events']->listen(UploadFailed::class, function (UploadFailed $event) {
            $this->app['log']->error($event->message, [
                'source'  => $event->sourcePath,
                'storage' => $event->storage->id,
                'driver'  => $event->storage->driver,
                'owner'   => $event->storage->owner_description,
            ]);
        });

        $this->app['events']->listen(CloudStorageDisabled::class, function (CloudStorageDisabled $event) {
            $this->app['log']->warning("Connection disabled: " . $event->message, [
                'reason'  => $event->storage->reason,
                'storage' => $event->storage->id,
                'driver'  => $event->storage->driver,
                'owner'   => $event->storage->owner_description,
            ]);
        });

        $this->app['events']->listen(CloudStorageEnabled::class, function (CloudStorageEnabled $event) {
            $this->app['log']->info("Connection enabled", [
                'storage' => $event->storage->id,
                'driver'  => $event->storage->driver,
                'owner'   => $event->storage->owner_description,
            ]);
        });
    }
}
