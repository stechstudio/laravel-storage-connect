<?php

namespace STS\StorageConnect\Models;

use Carbon\Carbon;

/**
 * Class CustomManagedCloudStorage
 * @package STS\StorageConnect\Models
 */
class CustomManagedCloudStorage extends CloudStorage
{
    /**
     * @var callable
     */
    protected $saveCallback;

    /**
     * @param string $driver
     * @param callable $saveCallback
     *
     * @return CustomManagedCloudStorage
     */
    public static function init($driver, $saveCallback)
    {
        return (new static)
            ->setSaveCallback($saveCallback)
            ->fill([
                'driver' => $driver,
                'id'     => 0
            ]);
    }

    /**
     * @param array $attributes
     * @param callable $saveCallback
     *
     * @return $this
     */
    public function restore(array $attributes, $saveCallback)
    {
        $this->fill($attributes);
        $this->saveCallback = $saveCallback;
        $this->exists = true;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return void
     */
    public function save(array $options = [])
    {
        if (!$this->created_at) {
            $this->setCreatedAt(Carbon::now());
        }
        $this->setUpdatedAt(Carbon::now());

        call_user_func_array($this->saveCallback, [$this->toJson(), $this->driver]);
    }

    /**
     * @param $callback
     *
     * @return $this
     */
    public function setSaveCallback($callback)
    {
        $this->saveCallback = $callback;

        return $this;
    }
}