<?php

namespace STS\StorageConnect\Connections;

use Carbon\Carbon;
use Exception;
use STS\Backoff\Backoff;
use STS\Backoff\Strategies\PolynomialStrategy;
use STS\StorageConnect\Events\ConnectionDisabled;
use STS\StorageConnect\Events\ConnectionEnabled;
use STS\StorageConnect\Events\RetryingUpload;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Exceptions\ConnectionUnavailableException;
use STS\StorageConnect\Jobs\UploadFile;
use STS\StorageConnect\Providers\DropboxProvider;
use Log;
use Queue;
use AWS;

/**
 * Class AbstractConnection
 * @package STS\StorageConnect\Connections
 */
abstract class AbstractConnection
{
    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var DropboxProvider
     */
    protected $provider;

    /**
     * @var object
     */
    protected $job;

    /**
     * AbstractConnection constructor.
     *
     * @param $provider
     */
    public function __construct( $provider )
    {
        $this->provider = $provider;
        $this->provider->setConnection($this);
    }

    /**
     * @param $redirectUrl
     *
     * @return mixed
     */
    public function authorize($redirectUrl = null)
    {
        return $this->provider()->authorize($redirectUrl, $this);
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return (bool) count($this->config);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isConnected() && $this->status == "enabled";
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->isConnected() && $this->status == "disabled";
    }

    public function isFull()
    {
        return $this->isDisabled() && $this->reason == "full";
    }

    /**
     * @return float
     */
    public function percentFull()
    {
        return $this->provider()->percentFull();
    }

    /**
     * Ensures we have a valid and enabled connection. If it was disabled due to space full,
     * see if we should check again.
     */
    public function verify()
    {
        if($this->isFull() && $this->quotaLastCheckedAt->diffInMinutes(Carbon::now()) > 60) {
            $this->checkStorageQuota();
        }

        return $this->isEnabled();
    }

    public function checkStorageQuota()
    {
        if($this->percentFull() < 99) {
            $this->enable();
        } else {
            $this->quotaLastCheckedAt = Carbon::now();
            $this->save();
        }
    }

    /**
     * @throws ConnectionUnavailableException
     */
    public function verifyOrFail()
    {
        if(!$this->verify()) {
            throw new ConnectionUnavailableException($this);
        }
    }

    /**
     * @param $owner
     *
     * @return $this
     */
    public function belongsTo( $owner )
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Illuminate\Database\Eloquent\Model
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * @return mixed
     */
    public function provider()
    {
        if (!$this->provider) {
            $this->provider = $this->manager->driver($this->name);
        }

        return $this->provider;
    }

    /**
     * @param $config
     *
     * @return $this
     */
    public function unserialize( $config )
    {
        return $this->load((array) json_decode($config, true));
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function load( array $config )
    {
        $this->config = $config;

        foreach(['lastUploadAt', 'disabledAt', 'quotaLastCheckedAt'] as $dtField) {
            if(isset($this->config[$dtField])) {
                $this->config[$dtField] = new Carbon($this->config[$dtField]['date'], $this->config[$dtField]['timezone']);
            }
        }

        if(!isset($this->config['status'])) {
            $this->config['status'] = "enabled";
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        if ($this->owner) {
            $this->owner->setStorageConnection($this->name, $this)->save();
        } else {
            app('sts.storage-connect')->saveConnectedStorage($this, $this->name());
        }

        return $this;
    }

    /**
     * @param $job
     *
     * @return $this
     */
    public function setJob( $job )
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->config);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function identify()
    {
        return $this->owner
            ? $this->name() . ':' . (new \ReflectionClass($this->owner))->getShortName() . ":" . $this->owner->getKey()
            : $this->name() . ':' . $this->email;
    }

    /**
     * @param      $sourcePath
     * @param      $remotePath
     * @param bool $queued
     *
     * @return bool
     * @throws ConnectionUnavailableException
     */
    public function upload( $sourcePath, $remotePath, $queued = true )
    {
        $this->verifyOrFail();

        if ($queued) {
            return Queue::push(new UploadFile($sourcePath, $remotePath, $this));
        }

        if(starts_with($sourcePath, "s3://")) {
            app('aws')->createClient('s3')->registerStreamWrapper();
        }

        try {
            $this->provider->upload($sourcePath, $remotePath);

            $this->lastUploadAt = new Carbon();
            $this->save();

            event(new UploadSucceeded($this, $sourcePath, $remotePath));

            return true;
        } catch (Exception $e) {
            $this->handleUploadError($e, $sourcePath);

            if (!$this->job) {
                throw $e;
            }
        }
    }

    /**
     * @param $message
     * @param $exception
     * @param $sourcePath
     */
    protected function retry( $message, $exception, $sourcePath )
    {
        if ($this->job) {
            event(new RetryingUpload($this, $message, $exception, $sourcePath));

            $this->job->release(
                (new Backoff)
                    ->setStrategy(new PolynomialStrategy(5, 3))
                    ->setWaitCap(900)
                    ->setJitter(true)
                    ->getWaitTime($this->job->attempts())
            );
        } else {
            event(new UploadFailed($this, $message, $exception, $sourcePath));
        }
    }

    /**
     * @param $message
     * @param $reason
     *
     * @return $this
     */
    public function disable( $message, $reason )
    {
        $this->status = "disabled";
        $this->reason = $reason;
        $this->disabledAt = new Carbon();

        if($reason == "full") {
            $this->quotaLastCheckedAt = Carbon::now();
        }

        $this->save();

        event(new ConnectionDisabled($this, $reason, $message));

        return $this;
    }

    /**
     * @return $this
     */
    public function enable()
    {
        $this->status = "enabled";
        array_forget($this->config, ['reason', 'disabledAt', 'quotaLastCheckedAt']);

        $this->save();

        event(new ConnectionEnabled($this));

        return $this;
    }

    /**
     * @param Exception $e
     * @param $sourcePath
     *
     * @return mixed
     */
    abstract protected function handleUploadError(Exception $e, $sourcePath);

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * The provider has an instance of the Laravel application, as well as
     * the StorageConnectManager, both of which contain Closures. So before
     * we are serialized, detach all this.
     */
    public function __sleep()
    {
        $this->providerName = $this->provider->name();
        $this->provider = null;

        return array_keys(get_object_vars($this));
    }

    /**
     * After being unserialized create a fresh provider connection
     */
    public function __wakeup()
    {
        $this->provider = app('sts.storage-connect')->driver($this->providerName);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function __get( $key )
    {
        return array_get($this->config, $key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set( $key, $value )
    {
        array_set($this->config, $key, $value);
    }
}