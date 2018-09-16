<?php

namespace STS\StorageConnect\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use STS\Backoff\Backoff;
use STS\Backoff\Strategies\PolynomialStrategy;
use STS\StorageConnect\Adapters\Adapter;
use STS\StorageConnect\Events\CloudStorageDisabled;
use STS\StorageConnect\Events\CloudStorageEnabled;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Exceptions\StorageUnavailableException;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Jobs\UploadFile;
use STS\StorageConnect\StorageConnectFacade;
use STS\StorageConnect\Types\Quota;

/**
 * Class CloudStorage
 * @package STS\StorageConnect\Models
 */
class CloudStorage extends Model
{
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
        'token' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * @return Adapter
     */
    public function adapter()
    {
        return StorageConnectFacade::adapter($this->driver)->setToken((array)$this->token, function ( $token ) {
            $this->update(['token' => $token]);
        });
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
    public function authorize( $redirectUrl = null )
    {
        return $this->adapter()->authorize($this, $redirectUrl);
    }

    /**
     * @param null $reason
     *
     * @return $this
     */
    public function disable( $reason = null )
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
            $this->checkSpace();
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
    public function checkSpace()
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
     * @param      $sourcePath
     * @param      $destinationPath
     * @param bool $queue
     * @param null $queuedJob
     *
     * @return bool
     * @throws StorageUnavailableException
     */
    public function upload( $sourcePath, $destinationPath, $queue = true, $queuedJob = null )
    {
        if (!$this->verify()) {
            throw new StorageUnavailableException($this);
        }

        if ($queue) {
            return dispatch(new UploadFile($sourcePath, $destinationPath, $this));
        }

        try {
            return $this->handleUpload($sourcePath, $destinationPath);
        } catch (UploadException $e) {
            $this->handleUploadError($e, $queuedJob);
        }

        return false;
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return bool
     */
    protected function handleUpload( $sourcePath, $destinationPath )
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
     * @param UploadException $e
     * @param null            $job
     */
    protected function handleUploadError( UploadException $e, $job = null )
    {
        $e->setStorage($this);

        if ($e->shouldRetry() && $job) {
            event(new UploadRetrying($this, $e));

            $job->release();
        }

        if ($e->shouldDisable()) {
            $this->disable($e->getReason());
        }

        if ($job) {
            $job->delete();
        }

        event(new UploadFailed($this, $e));
    }
}