<?php

namespace STS\StorageConnect\Events;

use Illuminate\Database\Eloquent\Model;
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
     * @var Model
     */
    public $target;

    /**
     * RetryingUpload constructor.
     *
     * @param CloudStorage $storage
     * @param UploadException $exception
     */
    public function __construct(CloudStorage $storage, UploadException $exception )
    {
        $this->message = $exception->getMessage();
        $this->storage = $storage;
        $this->exception = $exception;
        $this->sourcePath = $exception->getRequest()->getSourcePath();
        $this->target = $exception->getRequest()->getTarget();
    }
}