<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Models\CloudStorage;

/**
 * Class CloudStorageDisabled
 * @package STS\StorageConnect\Events
 */
class CloudStorageDisabled
{
    /**
     * @var CloudStorage
     */
    public $storage;

    /**
     * ConnectionDisabled constructor.
     *
     * @param CloudStorage $storage
     */
    public function __construct(CloudStorage $storage)
    {
        $this->storage = $storage;
    }
}