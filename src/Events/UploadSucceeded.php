<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\Connection;

/**
 * Class UploadSucceeded
 * @package STS\StorageConnect\Events
 */
class UploadSucceeded
{
    /**
     * @var Connection
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
     * @param Connection $connection
     * @param                    $sourcePath
     * @param $destinationPath
     */
    public function __construct(Connection $connection, $sourcePath, $destinationPath)
    {
        $this->connection = $connection;
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
    }
}