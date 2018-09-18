<?php

namespace STS\StorageConnect\Models;

use Carbon\Carbon;
use StorageConnect;
use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Events\CloudStorageDisabled;
use STS\StorageConnect\Events\CloudStorageEnabled;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Exceptions\StorageUnavailableException;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Jobs\UploadFile;

/**
 * Class CloudStorage
 * @package STS\StorageConnect\Models
 */
class CloudStorage extends Model
{
    /**
     *
     */
    const SPACE_FULL = "full";
    /**
     *
     */
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
     * @var
     */
    protected $adapter;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * @return string
     */
    public function getOwnerDescriptionAttribute()
    {
        return $this->owner
            ? array_reverse(explode("\\", $this->owner_type))[0] . ":" . $this->owner_id
            : $this->email;
    }

    /**
     * @param null $redirectUrl
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authorize($redirectUrl = null)
    {
        return $this->adapter()->authorize($this, $redirectUrl);
    }

    /**
     * @return mixed
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isConnected() && $this->enabled;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isTokenInvalid()
    {
        return $this->isDisabled() && $this->reason == self::INVALID_TOKEN;
    }

    /**
     * @return mixed
     */
    public function isFull()
    {
        return $this->full;
    }

    /**
     * @return mixed
     */
    public function percentFull()
    {
        return $this->percent_full;
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

    /**
     * @param null $reason
     *
     * @return $this
     */
    public function disable($reason = null)
    {
        $this->enabled = 0;
        $this->reason = $reason;

        if ($reason == self::SPACE_FULL) {
            $this->full = 1;
        }

        $this->save();
        event(new CloudStorageDisabled($this));

        return $this;
    }

    /**
     * @return $this
     */
    public function enable()
    {
        $this->reason = null;
        $this->enabled = 1;
        $this->full = 0;

        $this->save();
        event(new CloudStorageEnabled($this));

        return $this;
    }

    /**
     * @return bool
     */
    public function verify()
    {
        if ($this->full && $this->shouldCheckSpace()) {
            $this->checkSpaceUsage();
        }

        return $this->enabled;
    }

    /**
     * @return bool
     */
    protected function shouldCheckSpace()
    {
        return $this->space_checked_at && $this->space_checked_at->diffInMinutes(Carbon::now()) > 60;
    }

    /**
     * @return $this
     */
    public function checkSpaceUsage()
    {
        $this->update($this->adapter()->getQuota()->toArray());

        if ($this->full && $this->percent_full < 99) {
            $this->enable();
        } else if (!$this->full && $this->percent_full > 99) {
            $this->disable(self::SPACE_FULL);
        }

        return $this;
    }

    /**
     * @return AbstractAdapter
     */
    public function adapter()
    {
        if (!$this->adapter) {
            $this->adapter = StorageConnect::adapter($this->driver)->setToken((array)$this->token, function ($token) {
                $this->update(['token' => $token]);
            });
        }

        return $this->adapter;
    }

    /**
     * @param      $sourcePath
     * @param      $destinationPath
     * @param bool $shouldQueue
     * @param null $queueJob
     *
     * @return bool
     * @throws StorageUnavailableException
     */
    public function upload($sourcePath, $destinationPath, $shouldQueue = true, $queueJob = null)
    {
        if (!$this->verify()) {
            throw new StorageUnavailableException($this);
        }

        if ($shouldQueue) {
            return dispatch(new UploadFile($sourcePath, $destinationPath, $this));
        }

        try {
            return $this->handleUpload($sourcePath, $destinationPath);
        } catch (UploadException $exception) {
            $this->handleUploadError($exception, $queueJob);
        }

        return false;
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return bool
     */
    protected function handleUpload($sourcePath, $destinationPath)
    {
        if (starts_with($sourcePath, "s3://")) {
            app('aws')->createClient('s3')->registerStreamWrapper();
        }

        $this->adapter()->upload($sourcePath, $destinationPath);

        $this->uploaded_at = Carbon::now();
        $this->save();

        event(new UploadSucceeded($this, $sourcePath, $destinationPath));

        return true;
    }

    /**
     * @param UploadException $exception
     * @param null $job
     *
     * @return mixed
     */
    protected function handleUploadError(UploadException $exception, $job = null)
    {
        $exception->setStorage($this);

        if ($exception->shouldRetry() && $job) {
            event(new UploadRetrying($this, $exception, $exception->getSourcePath()));

            $job->release();

            return;
        }

        if ($exception->shouldDisable()) {
            $this->disable($exception->getReason());
        }

        if ($job) {
            $job->fail($exception);
        }

        event(new UploadFailed($this, $exception, $exception->getSourcePath()));
    }

    public function __sleep()
    {
        $this->adapter = null;

        return array_keys(get_object_vars($this));
    }
}