<?php
namespace STS\StorageConnect\Events;

use STS\StorageConnect\Models\CloudStorage;

class CloudStorageSetup
{
    /**
     * @var CloudStorage
     */
    public $storage;

    /**
     * StorageConnected constructor.
     *
     * @param CloudStorage $storage
     */
    public function __construct(CloudStorage $storage)
    {
        $this->storage = $storage;
    }
}