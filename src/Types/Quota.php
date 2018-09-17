<?php
namespace STS\StorageConnect\Types;

use Carbon\Carbon;

/**
 * Class Quota
 * @package STS\StorageConnect\Types
 */
class Quota
{
    /**
     * @var int
     */
    protected $total;
    /**
     * @var int
     */
    protected $used;

    /**
     * @var Carbon
     */
    protected $checked;

    /**
     * Quota constructor.
     *
     * @param $total
     * @param $used
     */
    public function __construct( $total, $used)
    {
        $this->total = $total;
        $this->used = $used;
        $this->checked = Carbon::now();
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * @return int
     */
    public function getAvailable()
    {
        return $this->total - $this->used;
    }

    /**
     * @return float|int
     */
    public function getPercentFull()
    {
        return $this->total > 0
            ? round(($this->used / $this->total) * 100, 1)
            : 0;
    }

    /**
     * @return Carbon
     */
    public function getCheckedAt()
    {
        return $this->checked;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'total_space'      => $this->getTotal(),
            'space_used'       => $this->getUsed(),
            'space_available'  => $this->getAvailable(),
            'percent_full'     => $this->getPercentFull(),
            'space_checked_at' => $this->getCheckedAt()
        ];
    }
}