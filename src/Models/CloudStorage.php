<?php

namespace STS\StorageConnect\Models;

use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Exceptions\StorageUnavailableException;

/**
 * Class CloudStorage
 * @package STS\StorageConnect\Models
 */
class CloudStorage extends Model
{
    use Concerns\UploadsFiles,
        Concerns\TracksQuota,
        Concerns\HasStorageAdapter,
        Concerns\ManagesStorageConnection;

    const SPACE_FULL = "full";
    const INVALID_TOKEN = "invalid";

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'token'     => 'array',
        'connected' => 'boolean',
        'enabled'   => 'boolean',
        'full'      => 'boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * If we have an owner, use the owning model name and identifier.
     * Otherwise we'll have to just use the cloud storage email address.
     *
     * @return string
     */
    public function getOwnerDescriptionAttribute()
    {
        return $this->owner
            ? array_reverse(explode("\\", $this->owner_type))[0] . ":" . $this->owner_id
            : $this->email;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUserEmail()
    {
        return $this->email;
    }
}