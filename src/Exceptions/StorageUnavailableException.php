<?php

namespace STS\StorageConnect\Exceptions;

use Illuminate\Support\Arr;
use STS\StorageConnect\Connections\Connection;
use STS\StorageConnect\Models\CloudStorage;
use Throwable;

/**
 * Class StorageUnavailableException
 * @package STS\StorageConnect\Exceptions
 */
class StorageUnavailableException extends \Exception
{
    /**
     * StorageUnavailableException constructor.
     *
     * @param CloudStorage $storage
     */
    public function __construct(CloudStorage $storage)
    {
        $this->message = $this->why($storage);
    }

    /**
     * @param CloudStorage $storage
     *
     * @return string
     */
    protected function why(CloudStorage $storage)
    {
        if(!$storage->connected) {
            return "Connection is not set up";
        }

        if(!$storage->enabled) {
            return "Connection has been disabled: " . $this->reason($storage) . " [" . $storage->owner_description . "]";
        }
    }

    /**
     * @param CloudStorage $storage
     *
     * @return mixed
     */
    protected function reason(CloudStorage $storage)
    {
        return Arr::get([
            'invalid' => 'Connection is invalid, please re-authorize',
            'full' => 'Storage is full, please clear space or upgrade storage account'
        ], $storage->reason,'unknown reason');
    }
}