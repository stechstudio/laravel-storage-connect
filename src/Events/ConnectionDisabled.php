<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\Connection;

/**
 * Class ConnectionDisabled
 * @package STS\StorageConnect\Events
 */
class ConnectionDisabled
{
    /**
     * @var Connection
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
     * @param Connection $connection
     * @param                    $reason
     * @param                    $message
     */
    public function __construct(Connection $connection, $reason, $message )
    {
        $this->connection = $connection;
        $this->reason = $reason;
        $this->message = $message;
    }
}