<?php

namespace STS\StorageConnect;

use STS\Backoff\Backoff;
use STS\Backoff\Strategies\PolynomialStrategy;

class UploadResponse
{
    /**
     * @var UploadRequest
     */
    protected $request;

    /**
     * Original response from storage provider
     *
     * @var mixed
     */
    protected $original;

    /**
     * @var bool
     */
    protected $async = false;

    /**
     * @var int
     */
    protected $statusChecks = 0;

    public function __construct( UploadRequest $request, $original, $async = false )
    {
        $this->request = $request;
        $this->original = $original;
        $this->async = $async;
    }

    /**
     * @return bool
     */
    public function isAsync()
    {
        return $this->async;
    }

    /**
     *
     */
    public function incrementStatusCheck()
    {
        $this->statusChecks++;
    }

    /**
     * @return int
     */
    public function getStatusChecks()
    {
        return $this->statusChecks;
    }

    /**
     * @return int
     */
    public function getNextCheckDelay()
    {
        return (new Backoff)
            ->setStrategy(new PolynomialStrategy(30, 2))
            ->setWaitCap(600)
            ->getWaitTime($this->getStatusChecks());
    }

    /**
     * @return UploadRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }
}