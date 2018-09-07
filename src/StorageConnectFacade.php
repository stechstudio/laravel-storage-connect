<?php

namespace STS\StorageConnect;

use Illuminate\Support\Facades\Facade;

/**
 * @see \STS\StorageConnect\StorageConnectFacade
 */
class StorageConnectFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sts.storage-connect';
    }
}
