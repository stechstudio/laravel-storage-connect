<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Models\CloudStorage;

/**
 * Class UploadFailed
 * @package STS\StorageConnect\Events
 */
class UploadFailed
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
     * @var \Exception
     */
    public $exception;

    /**
     * @var string
     */
    public $sourcePath;

    /**
     * UploadFailed constructor.
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