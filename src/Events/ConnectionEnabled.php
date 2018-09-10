<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\AbstractConnection;

/**
 * Class ConnectionEnabled
 * @package STS\StorageConnect\Events
 */
class ConnectionEnabled
{
    /**
     * @var AbstractConnection
     */
    public $connection;

    /**
     * ConnectionDisabled constructor.
     *
     * @param AbstractConnection $connection
     */
    public function __construct( AbstractConnection $connection )
    {
        $this->connection = $connection;
    }
}