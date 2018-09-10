<?php
namespace STS\StorageConnect\Events;

use STS\StorageConnect\Connections\AbstractConnection;

class StorageConnected
{
    /**
     * @var AbstractConnection
     */
    public $connection;

    /**
     * @var string
     */
    public $driver;

    /**
     * StorageConnected constructor.
     *
     * @param AbstractConnection $connection
     * @param $driver
     */
    public function __construct(AbstractConnection $connection, $driver)
    {
        $this->connection = $connection;
        $this->driver = $driver;
    }
}