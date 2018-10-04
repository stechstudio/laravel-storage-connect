<?php

namespace STS\StorageConnect\Exceptions;

use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\UploadRequest;
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
     * @var UploadRequest
     */
    protected $request;

    public function __construct(UploadRequest $request, $previous = null)
    {
        $this->request = $request;
        parent::__construct(null, null, $previous);
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function message ( $message )
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function retry( $message )
    {
        $this->retry = true;
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

    /**
     * @return UploadRequest
     */
    public function getRequest()
    {
        return $this->request;
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