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
     * @var CloudStorage
     */
    public $storage;

    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * UploadFailed constructor.
     *
     * @param CloudStorage $storage
     * @param UploadException $exception
     */
    public function __construct(CloudStorage $storage, UploadException $exception)
    {
        $this->storage = $storage;
        $this->exception = $exception;
    }
}