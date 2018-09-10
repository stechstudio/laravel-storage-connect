<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\AbstractConnection;

/**
 * Class UploadFailed
 * @package STS\StorageConnect\Events
 */
class UploadFailed
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
     * @var \Exception
     */
    protected $exception;

    /**
     * UploadFailed constructor.
     *
     * @param AbstractConnection $connection
     * @param                    $message
     * @param \Exception $exception
     * @param                    $sourcePath
     */
    public function __construct(AbstractConnection $connection, $message, $exception, $sourcePath)
    {
        $this->connection = $connection;
        $this->message = $message;
        $this->sourcePath = $sourcePath;
        $this->exception = $exception;
    }
}