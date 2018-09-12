<?php

namespace STS\StorageConnect\Exceptions;

use STS\StorageConnect\Connections\Connection;
use Throwable;

/**
 * Class ConnectionUnavailableException
 * @package STS\StorageConnect\Exceptions
 */
class ConnectionUnavailableException extends \Exception
{
    /**
     * ConnectionUnavailableException constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->message = $this->why($connection);
    }

    /**
     * @param Connection $connection
     *
     * @return string
     */
    protected function why(Connection $connection)
    {
        if(!$connection->isConnected()) {
            return "Connection is not set up";
        }

        if($connection->isDisabled()) {
            return "Connection has been disabled: " . $this->reason($connection);
        }
    }

    /**
     * @param Connection $connection
     *
     * @return mixed
     */
    protected function reason(Connection $connection)
    {
        return array_get([
            'invalid' => 'Connection is invalid, please re-authorize',
            'full' => 'Storage is full, please clear space or upgrade storage account'
        ], $connection->reason,'unknown reason');
    }
}