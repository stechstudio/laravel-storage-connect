<?php

namespace STS\StorageConnect\Connections;

use Exception;
use STS\Backoff\Backoff;
use STS\Backoff\Strategies\PolynomialStrategy;
use STS\StorageConnect\Jobs\UploadFile;
use STS\StorageConnect\Providers\DropboxProvider;
use Log;
use Queue;

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
     * @var array
     */
    protected $config = [];

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
        return $this->load(json_decode($config, true));
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function load( array $config )
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        if ($this->owner) {
            $this->owner->setStorageConnection($this->name, $this);
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
    public function indentify()
    {
        return $this->owner
            ? (new \ReflectionClass($this->owner))->getShortName() . ":" . $this->owner->getKey()
            : $this->email;
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

    /**
     * @param $message
     * @param $localPath
     */
    protected function retry( $message, $localPath )
    {
        Log::warning($message, [
            "path"       => $localPath,
            "driver"     => $this->name,
            "connection" => $this->identify()
        ]);

        if ($this->job) {
            $this->job->release(
                (new Backoff)
                    ->setStrategy(new PolynomialStrategy(5, 3))
                    ->setWaitCap(900)
                    ->setJitter(true)
                    ->getWaitTime($this->job->attempts())
            );
        }
    }

    /**
     * @param $message
     * @param $reason
     */
    protected function disable( $message, $reason )
    {
        Log::error($message, [
            "driver"     => $this->name,
            "connection" => $this->identify()
        ]);

        $this->status = "disabled";
        $this->reason = $reason;

        $this->save();
    }

    /**
     * @param      $sourcePath
     * @param      $remotePath
     * @param bool $queued
     *
     * @return bool
     */
    public function upload( $sourcePath, $remotePath, $queued = true )
    {
        if ($queued) {
            return Queue::push(new UploadFile($sourcePath, $remotePath, $this));
        }

        try {
            $this->provider->upload($sourcePath, $remotePath);

            return true;
        } catch (Exception $e) {
            $this->handleUploadError($e, $sourcePath);
        }
    }

    abstract protected function handleUploadError(Exception $e, $sourcePath);
}