<?php

namespace STS\StorageConnect\Events;

use Illuminate\Database\Eloquent\Model;
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
     * @var Model
     */
    public $target;

    /**
     * UploadFailed constructor.
     *
     * @param CloudStorage $storage
     * @param                    $sourcePath
     * @param $destinationPath
     * @param null $target
     */
    public function __construct(CloudStorage $storage, $sourcePath, $destinationPath, $target = null)
    {
        $this->storage = $storage;
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
        $this->target = $target;
    }
}