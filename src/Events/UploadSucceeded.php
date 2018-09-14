<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Models\CloudStorage;

/**
 * Class UploadSucceeded
 * @package STS\StorageConnect\Events
 */
class UploadSucceeded
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
     * UploadFailed constructor.
     *
     * @param CloudStorage $storage
     * @param                    $sourcePath
     * @param $destinationPath
     */
    public function __construct(CloudStorage $storage, $sourcePath, $destinationPath)
    {
        $this->storage = $storage;
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
    }
}