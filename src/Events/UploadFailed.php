<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\Connection;

/**
 * Class UploadFailed
 * @package STS\StorageConnect\Events
 */
class UploadFailed
{
    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $sourcePath;

    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * UploadFailed constructor.
     *
     * @param Connection $connection
     * @param                    $message
     * @param \Exception $exception
     * @param                    $sourcePath
     */
    public function __construct(Connection $connection, $message, $exception, $sourcePath)
    {
        $this->connection = $connection;
        $this->message = $message;
        $this->sourcePath = $sourcePath;
        $this->exception = $exception;
    }
}