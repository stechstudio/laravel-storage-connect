<?php

namespace STS\StorageConnect;

use Illuminate\Support\ServiceProvider;
use STS\StorageConnect\Events\ConnectionDisabled;
use STS\StorageConnect\Events\ConnectionEnabled;
use STS\StorageConnect\Events\RetryingUpload;
use STS\StorageConnect\Events\StorageConnected;
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
            $this->publishes([
                __DIR__ . '/../config/storage-connect.php' => config_path('storage-connect.php'),
            ], 'config');
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
        $this->app['events']->listen(StorageConnected::class, function (StorageConnected $event) {
            $this->app['log']->info("New cloud storage connection", [
                'connection' => $event->connection->identify()
            ]);
        });

        $this->app['events']->listen(UploadSucceeded::class, function (UploadSucceeded $event) {
            $this->app['log']->info("File uploaded to cloud storage", [
                'connection'  => $event->connection->identify(),
                'source'      => $event->sourcePath,
                'destination' => $event->destinationPath
            ]);
        });

        $this->app['events']->listen(RetryingUpload::class, function (RetryingUpload $event) {
            $this->app['log']->warning($event->message, [
                'source'     => $event->sourcePath,
                'connection' => $event->connection->identify()
            ]);
        });

        $this->app['events']->listen(UploadFailed::class, function (UploadFailed $event) {
            $this->app['log']->error($event->message, [
                'source'     => $event->sourcePath,
                'connection' => $event->connection->identify()
            ]);
        });

        $this->app['events']->listen(ConnectionDisabled::class, function (ConnectionDisabled $event) {
            $this->app['log']->warning("Connection disabled: " . $event->message, [
                'reason'     => $event->reason,
                'connection' => $event->connection->identify()
            ]);
        });

        $this->app['events']->listen(ConnectionEnabled::class, function (ConnectionEnabled $event) {
            $this->app['log']->info("Connection enabled", [
                'connection' => $event->connection->identify()
            ]);
        });
    }
}
