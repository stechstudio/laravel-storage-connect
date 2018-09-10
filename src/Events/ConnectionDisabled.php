<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\AbstractConnection;

/**
 * Class ConnectionDisabled
 * @package STS\StorageConnect\Events
 */
class ConnectionDisabled
{
    /**
     * @var AbstractConnection
     */
    public $connection;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var string
     */
    public $message;

    /**
     * ConnectionDisabled constructor.
     *
     * @param AbstractConnection $connection
     * @param                    $reason
     * @param                    $message
     */
    public function __construct( AbstractConnection $connection, $reason, $message )
    {
        $this->connection = $connection;
        $this->reason = $reason;
        $this->message = $message;
    }
}