<?php

namespace STS\StorageConnect\Exceptions;

use STS\StorageConnect\Connections\AbstractConnection;
use Throwable;

class ConnectionUnavailableException extends \Exception
{
    public function __construct(AbstractConnection $connection)
    {
        $this->message = $this->why($connection);
    }

    protected function why(AbstractConnection $connection)
    {
        if(!$connection->isConnected()) {
            return "Connection is not set up";
        }

        if($connection->isDisabled()) {
            return "Connection has been disabled: " . $this->reason($connection);
        }
    }

    protected function reason(AbstractConnection $connection)
    {

    }
}