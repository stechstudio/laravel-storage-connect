<?php

namespace STS\StorageConnect\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use STS\StorageConnect\Models\CloudStorage;

/**
 * Class ConnectsToCloudStorage
 * @package STS\StorageConnect\Traits
 */
trait ConnectsToCloudStorage
{
    protected $supportedCloudStorage = ["dropbox", "google"];

    /**
     * @return MorphOne
     */
    public function dropbox()
    {
        return $this->morphOne(CloudStorage::class, 'owner')->whereDriver('dropbox')->withDefault([
            'driver' => 'dropbox',
        ]);
    }

    /**
     * @return MorphOne
     */
    public function google()
    {
        return $this->morphOne(CloudStorage::class, 'owner')->whereDriver('google')->withDefault([
            'drive' => 'google',
        ]);
    }

    /**
     * Alias
     *
     * @return MorphOne
     */
    public function googleDrive()
    {
        return $this->google();
    }

    /**
     * @param $driver
     *
     * @return CloudStorage
     */
    public function getCloudStorage($driver)
    {
        if (in_array($driver, $this->supportedCloudStorage)) {
            return $this->{$driver};
        }
    }
}