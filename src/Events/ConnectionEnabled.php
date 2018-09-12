<?php

namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\Connection;

/**
 * Class ConnectionEnabled
 * @package STS\StorageConnect\Events
 */
class ConnectionEnabled
{
    /**
     * @var Connection
     */
    public $connection;

    /**
     * ConnectionDisabled constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection )
    {
        $this->connection = $connection;
    }
}