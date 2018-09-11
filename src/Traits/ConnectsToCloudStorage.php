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
     * @param $provider
     *
     * @return null
     */
    public function getStorageConnection($provider)
    {
        return StorageConnect::connection($provider)->belongsTo($this)->unserialize(
            $this->attributes[array_get($this->cloudStorageConnections, $provider)]
        );
    }

    /**
     * @param $provider
     * @param $connection
     *
     * @return null
     */
    public function setStorageConnection($provider, $connection)
    {
        if (!array_key_exists($provider, $this->cloudStorageConnections)) {
            return null;
        }

        $this->attributes[array_get($this->cloudStorageConnections, $provider)] = (string)$connection;

        return $this;
    }
}