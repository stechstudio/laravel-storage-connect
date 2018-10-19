<?php
namespace STS\StorageConnect\Subscribers;

use STS\StorageConnect\Events\CloudStorageDisabled;
use STS\StorageConnect\Events\CloudStorageEnabled;
use STS\StorageConnect\Events\CloudStorageSetup;
use Log;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadInProgress;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\UploadStarted;
use STS\StorageConnect\Events\UploadSucceeded;

/**
 * Class LogsActivity
 * @package STS\StorageConnect\Subscribers
 */
class LogsActivity
{
    /**
     * @param $dispatcher
     */
    public function subscribe($dispatcher)
    {
        $dispatcher->listen(CloudStorageSetup::class, function (CloudStorageSetup $event) {
            $this->info("New cloud storage connection", $event->storage);
        });

        $dispatcher->listen(UploadSucceeded::class, function (UploadSucceeded $event) {
            $this->info("File uploaded to cloud storage", $event->storage, [
                'source'      => $event->sourcePath,
                'destination' => $event->destinationPath
            ]);
        });

        $dispatcher->listen(UploadRetrying::class, function (UploadRetrying $event) {
            $this->warning($event->message, $event->storage, [
                'source'  => $event->sourcePath
            ]);
        });

        $dispatcher->listen(UploadFailed::class, function (UploadFailed $event) {
            $this->error($event->message, $event->storage, [
                'source'  => $event->sourcePath
            ]);
        });

        $dispatcher->listen(UploadStarted::class, function (UploadStarted $event) {
            $this->info("Async upload started", $event->storage, [
                'source'  => $event->sourcePath
            ]);
        });

        $dispatcher->listen(UploadInProgress::class, function (UploadInProgress $event) {
            $this->info("Async upload in progress", $event->storage, [
                'source'  => $event->sourcePath
            ]);
        });

        $dispatcher->listen(CloudStorageDisabled::class, function (CloudStorageDisabled $event) {
            $this->warning("Connection disabled", $event->storage, [
                'reason'  => $event->storage->reason
            ]);
        });

        $dispatcher->listen(CloudStorageEnabled::class, function (CloudStorageEnabled $event) {
            $this->info("Connection enabled", $event->storage);
        });
    }

    /**
     * @param $message
     * @param $storage
     * @param array $context
     */
    protected function info($message, $storage, array $context = [])
    {
        Log::info($message, $this->getContext($storage, $context));
    }

    /**
     * @param $message
     * @param $storage
     * @param array $context
     */
    protected function warning($message, $storage, array $context = [])
    {
        Log::warning($message, $this->getContext($storage, $context));
    }

    /**
     * @param $message
     * @param $storage
     * @param array $context
     */
    protected function error($message, $storage, array $context = [])
    {
        Log::error($message, $this->getContext($storage, $context));
    }

    /**
     * @param $storage
     * @param array $context
     *
     * @return array
     */
    protected function getContext($storage, array $context = [])
    {
        return array_merge($context, [
            'storage' => $storage->id,
            'driver'  => $storage->driver,
            'owner'   => $storage->owner_description,
        ]);
    }
}
