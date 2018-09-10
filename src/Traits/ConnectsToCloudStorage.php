<?php

namespace STS\StorageConnect\Traits;

use StorageConnect;

/**
 * Class ConnectsToCloudStorage
 * @package STS\StorageConnect\Traits
 */
trait ConnectsToCloudStorage
{
    /**
     * @return null
     */
    public function getDropboxConnectionAttribute()
    {
        return $this->getStorageConnection('dropbox');
    }

    /**
     * @param $connection
     */
    public function setDropboxConnectionAttribute($connection)
    {
        $this->setStorageConnection('dropbox', $connection);
    }

    /**
     * @param $driver
     *
     * @return null
     */
    public function getStorageConnection($driver)
    {
        return StorageConnect::connection($driver)->belongsTo($this)->unserialize(
            $this->attributes[array_get($this->cloudStorageConnections, $driver)]
        );
    }

    /**
     * @param $driver
     * @param $connection
     *
     * @return null
     */
    public function setStorageConnection($driver, $connection)
    {
        if (!array_key_exists($driver, $this->cloudStorageConnections)) {
            return null;
        }

        $this->attributes[array_get($this->cloudStorageConnections, $driver)] = (string)$connection;

        return $this;
    }
}