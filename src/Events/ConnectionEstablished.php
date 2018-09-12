<?php
namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\Connection;

class ConnectionEstablished
{
    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var string
     */
    public $driver;

    /**
     * StorageConnected constructor.
     *
     * @param Connection $connection
     * @param $driver
     */
    public function __construct(Connection $connection, $driver)
    {
        $this->connection = $connection;
        $this->driver = $driver;
    }
}