<?php
namespace STS\StorageConnect;

use Illuminate\Support\Manager;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Drivers\DropboxDriver;
use STS\StorageConnect\Events\StorageConnected;
use STS\StorageConnect\Providers\Dropbox;

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
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['storage-connect.default'];
    }

    /**
     * @return Dropbox
     */
    public function createDropboxDriver()
    {
        return new Dropbox($this->app['config']['services.dropbox'], $this, $this->app);
    }

    /**
     * @param $driver
     *
     * @return bool
     */
    public function isSupportedDriver($driver)
    {
        return in_array($driver, $this->app['config']['storage-connect.enabled']) && is_array($this->app['config']["services.$driver"]);
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

        event(new StorageConnected());
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
    public function load($driver)
    {
        return $this->driver($driver)->load((array) call_user_func($this->loadCallback, $driver));
    }
}