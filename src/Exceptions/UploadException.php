<?php

namespace STS\StorageConnect\Exceptions;

use STS\StorageConnect\Models\CloudStorage;
use Throwable;

/**
 * Class UploadException
 * @package STS\StorageConnect\Exceptions
 */
class UploadException extends \RuntimeException
{
    /**
     * @var bool
     */
    protected $retry = false;

    /**
     * @var bool
     */
    protected $disable = false;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var CloudStorage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $sourcePath;

    public function __construct($sourcePath, $previous = null)
    {
        $this->sourcePath = $sourcePath;
        parent::__construct(null, null, $previous);
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function retry( $message )
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param $message
     * @param null $reason
     */
    public function disable( $message, $reason = null)
    {
        $this->message = $message;
        $this->reason = $reason;
    }

    public function setSourcePath($path)
    {
        $this->sourcePath = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * @param CloudStorage $storage
     */
    public function setStorage(CloudStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return bool
     */
    public function shouldRetry()
    {
        return $this->retry;
    }

    /**
     * @return bool
     */
    public function shouldDisable()
    {
        return $this->disable;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}