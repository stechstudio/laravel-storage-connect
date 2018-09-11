<?php

namespace STS\StorageConnect\Exceptions;

use STS\StorageConnect\Connections\AbstractConnection;
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
     * @param AbstractConnection $connection
     */
    public function __construct(AbstractConnection $connection)
    {
        $this->message = $this->why($connection);
    }

    /**
     * @param AbstractConnection $connection
     *
     * @return string
     */
    protected function why(AbstractConnection $connection)
    {
        if(!$connection->isConnected()) {
            return "Connection is not set up";
        }

        if($connection->isDisabled()) {
            return "Connection has been disabled: " . $this->reason($connection);
        }
    }

    /**
     * @param AbstractConnection $connection
     *
     * @return mixed
     */
    protected function reason(AbstractConnection $connection)
    {
        return array_get([
            'invalid' => 'Connection is invalid, please re-authorize',
            'full' => 'Storage is full, please clear space or upgrade storage account'
        ], $connection->reason,'unknown reason');
    }
}