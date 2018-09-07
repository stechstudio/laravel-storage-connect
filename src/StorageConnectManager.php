<?php
namespace STS\StorageConnect;

use Illuminate\Support\Manager;
use STS\StorageConnect\Drivers\DropboxDriver;

/**
 * Class StorageConnectManager
 * @package STS\StorageConnect
 */
class StorageConnectManager extends Manager
{
    /**
     * @var callable
     */
    protected $stateCallback;

    /**
     * @var callable
     */
    protected $saveCallback;

    /**
     * @var callable
     */
    protected $loadCallback;

    /**
     * @var callable
     */
    protected $verifyCallback;

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['storage-connect.default'];
    }

    /**
     * @return DropboxDriver
     */
    public function createDropboxDriver()
    {
        return new DropboxDriver($this->app['config']['services.dropbox'], $this);
    }

    /**
     * @param $driver
     *
     * @return bool
     */
    public function isSupportedDriver($driver)
    {
        return in_array($driver, $this->app['config']['storage-connect.drivers']) && is_array($this->app['config']["services.$driver"]);
    }

    /**
     * @param string $driver
     *
     * @return mixed
     */
    protected function createDriver($driver)
    {
        if(!$this->isSupportedDriver($driver)) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        return parent::createDriver($driver);
    }

    /**
     * @param callable $callback
     */
    public function includeWithState($callback)
    {
        $this->stateCallback = $callback;
    }

    /**
     * @param $driver
     *
     * @return array
     */
    public function getCustomState($driver)
    {
        return isset($this->stateCallback)
            ? (array) call_user_func($this->stateCallback, $driver)
            : [];
    }

    /**
     * @param $callback
     */
    public function verifyNewConnectedStorageUsing($callback)
    {
        $this->verifyCallback = $callback;
    }

    /**
     * @param $driver
     *
     * @return string
     * @throws \Exception
     */
    public function finish($driver)
    {
        $state = $this->driver($driver)->getFinishedState();

        $this->driver($driver)->verifyCsrf($state);

        if(isset($this->verifyCallback) && call_user_func_array($this->verifyCallback, [$state, $driver]) === false) {
            throw new \Exception("Error connecting your cloud storage account");
        }

        $this->saveConnectedStorage($this->driver($driver)->finish(), $driver);

        return $this->app['config']['storage-connect.redirect_after_connect'];
    }

    /**
     * @param $callback
     */
    public function saveConnectedStorageUsing($callback)
    {
        $this->saveCallback = $callback;
    }

    /**
     * @param array $config
     * @param $driver
     */
    public function saveConnectedStorage(array $config, $driver)
    {
        call_user_func_array($this->saveCallback, [$config, $driver]);
    }

    /**
     * @param $callback
     */
    public function loadConnectedStorageUsing($callback)
    {
        $this->loadCallback = $callback;
    }

    /**
     * @param $driver
     *
     * @return mixed
     */
    public function loadConnectedStorage($driver)
    {
        return (array) call_user_func($this->loadCallback, $driver);
    }
}