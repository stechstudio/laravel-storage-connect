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
     * @var string
     */
    public $message;
    /**
     * @var CloudStorage
     */
    public $storage;

    /**
     * @var UploadException
     */
    public $exception;

    /**
     * @var string
     */
    public $sourcePath;

    /**
     * RetryingUpload constructor.
     *
     * @param CloudStorage $storage
     * @param UploadException $exception
     * @param $sourcePath
     */
    public function __construct(CloudStorage $storage, UploadException $exception, $sourcePath )
    {
        $this->message = $exception->getMessage();
        $this->storage = $storage;
        $this->exception = $exception;
        $this->sourcePath = $sourcePath;
    }
}