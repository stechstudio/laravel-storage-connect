<?php

namespace STS\StorageConnect\Events;

use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\UploadResponse;

/**
 * Class UploadInProgress
 * @package STS\StorageConnect\Events
 */
class UploadInProgress
{
    /**
     * @var CloudStorage
     */
    public $storage;

    /**
     * @var string
     */
    public $sourcePath;

    /**
     * @var string
     */
    public $destinationPath;

    /**
     * @var Model
     */
    public $target;

    /**
     * @var UploadResponse
     */
    public $response;

    /**
     * UploadSucceeded constructor.
     *
     * @param CloudStorage   $storage
     * @param UploadResponse $response
     */
    public function __construct(CloudStorage $storage, UploadResponse $response)
    {
        $this->storage = $storage;
        $this->sourcePath = $response->getRequest()->getSourcePath();
        $this->destinationPath = $response->getRequest()->getDestinationPath();
        $this->target = $response->getRequest()->getTarget();
        $this->response = $response;
    }
}