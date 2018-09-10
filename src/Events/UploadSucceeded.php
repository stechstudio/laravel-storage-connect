<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\AbstractConnection;

/**
 * Class UploadSucceeded
 * @package STS\StorageConnect\Events
 */
class UploadSucceeded
{
    /**
     * @var AbstractConnection
     */
    public $connection;

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
     * @param AbstractConnection $connection
     * @param                    $sourcePath
     * @param $destinationPath
     */
    public function __construct(AbstractConnection $connection, $sourcePath, $destinationPath)
    {
        $this->connection = $connection;
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
    }
}