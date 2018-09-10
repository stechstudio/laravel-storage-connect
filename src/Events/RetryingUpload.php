<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\AbstractConnection;

/**
 * Class RetryingUpload
 * @package STS\StorageConnect\Events
 */
class RetryingUpload
{
    /**
     * @var AbstractConnection
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
     * RetryingUpload constructor.
     *
     * @param AbstractConnection $connection
     * @param                    $message
     * @param                    $sourcePath
     */
    public function __construct( AbstractConnection $connection, $message, $sourcePath )
    {
        $this->connection = $connection;
        $this->message = $message;
        $this->sourcePath = $sourcePath;
    }
}