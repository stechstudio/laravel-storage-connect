<?php
namespace STS\StorageConnect\Models\Concerns;

use STS\StorageConnect\Events\CloudStorageDisabled;
use STS\StorageConnect\Events\CloudStorageEnabled;
use STS\StorageConnect\Exceptions\StorageUnavailableException;

trait ManagesStorageConnection
{
    /**
     * @return bool
     */
    public function isReady()
    {
        if ($this->isFull()) {
            $this->ping();
        }

        return $this->isEnabled();
    }

    /**
     * Sometimes we want to gracefully check up on the cloud storage account without any exceptions
     */
    public function ping()
    {
        if (!$this->shouldCheckSpace()) {
            return;
        }

        try {
            $this->checkSpaceUsage();
        } catch (\Exception $e) {
        }
    }

    /**
     * @return bool
     * @throws StorageUnavailableException
     */
    public function verify()
    {
        if (!$this->isReady()) {
            throw new StorageUnavailableException($this);
        }

        return true;
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
}