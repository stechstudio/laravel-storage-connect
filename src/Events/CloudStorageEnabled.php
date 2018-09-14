<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Models\CloudStorage;

/**
 * Class CloudStorageEnabled
 * @package STS\StorageConnect\Events
 */
class CloudStorageEnabled
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
    public function __construct(CloudStorage $storage )
    {
        $this->storage = $storage;
    }
}