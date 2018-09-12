<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\Connection;

/**
 * Class RetryingUpload
 * @package STS\StorageConnect\Events
 */
class UploadRetrying
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
    public $exception;

    /**
     * RetryingUpload constructor.
     *
     * @param Connection $connection
     * @param                    $message
     * @param $exception
     * @param                    $sourcePath
     */
    public function __construct(Connection $connection, $message, $exception, $sourcePath )
    {
        $this->connection = $connection;
        $this->message = $message;
        $this->sourcePath = $sourcePath;
        $this->exception = $exception;
    }
}