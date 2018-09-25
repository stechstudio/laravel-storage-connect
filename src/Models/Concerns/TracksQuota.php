<?php

namespace STS\StorageConnect\Models\Concerns;

use Carbon\Carbon;
use STS\StorageConnect\Types\Quota;

trait TracksQuota
{
    /**
     * @return bool
     */
    public function isFull()
    {
        return (bool) $this->full;
    }

    /**
     * @return mixed
     */
    public function percentFull()
    {
        return $this->percent_full;
    }

    /**
     * @return bool
     */
    public function shouldCheckSpace()
    {
        return
            // Never checked before
            !$this->space_checked_at

            // If we believe storage if full, check again after an hour
            || ($this->isFull() && $this->space_checked_at->diffInMinutes(Carbon::now()) > 60)

            // Otherwise just periodically check in
            || $this->space_checked_at->diffInHours(Carbon::now()) > 24;
    }

    /**
     *
     */
    public function checkSpaceUsage()
    {
        $this->updateQuota($this->adapter()->getQuota());
    }

    /**
     * @param Quota $quota
     *
     * @return $this
     */
    public function updateQuota(Quota $quota)
    {
        $this->fill($quota->toArray())->save();

        if ($this->full && $this->percent_full < 99) {
            $this->enable();
        } else if (!$this->full && $this->percent_full > 99) {
            $this->disable(self::SPACE_FULL);
        }

        return $this;
    }
}