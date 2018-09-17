<?php

namespace STS\StorageConnect\Models;

class CustomManagedCloudStorage extends CloudStorage
{
    /**
     * @var callable
     */
    protected $saveCallback;

    public static function setup($driver, $saveCallback)
    {
        return (new static)
            ->setSaveCallback($saveCallback)
            ->fill([
                'driver' => $driver,
                'id'     => 0
            ]);
    }

    /**
     * @return bool|void
     */
    public function save()
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