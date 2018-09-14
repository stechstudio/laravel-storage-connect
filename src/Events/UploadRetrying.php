<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Models\CloudStorage;

/**
 * Class RetryingUpload
 * @package STS\StorageConnect\Events
 */
class UploadRetrying
{
    /**
     * @var CloudStorage
     */
    public $storage;

    /**
     * @var UploadException
     */
    public $exception;

    /**
     * RetryingUpload constructor.
     *
     * @param CloudStorage $storage
     * @param UploadException $exception
     */
    public function __construct(CloudStorage $storage, UploadException $exception )
    {
        $this->storage = $storage;
        $this->exception = $exception;
    }
}