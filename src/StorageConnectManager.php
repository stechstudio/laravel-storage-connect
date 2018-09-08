<?php
namespace STS\StorageConnect;

use Illuminate\Support\Manager;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\AbstractConnection;
use STS\StorageConnect\Connections\DropboxConnection;
use STS\StorageConnect\Drivers\DropboxDriver;
use STS\StorageConnect\Events\StorageConnected;
use STS\StorageConnect\Providers\DropboxProvider;

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
     * @var array
     */
    protected $connections = [];

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['storage-connect.default'];
    }

    /**
     * @return DropboxProvider
     */
    public function createDropboxDriver()
    {
        return new DropboxProvider($this->app['config']['services.dropbox'], $this, $this->app);
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
     * @param AbstractConnection $connection
     * @param $driver
     */
    public function saveConnectedStorage(AbstractConnection $connection, $driver)
    {
        call_user_func_array($this->saveCallback, [$connection, $driver]);

        event(new StorageConnected($connection, $driver));
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

    /**
     * Get a connection instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function connection($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (! isset($this->connections[$driver])) {
            $this->connections[$driver] = $this->createConnection($driver);
        }

        return $this->connections[$driver];
    }

    /**
     * Create a new connection instance.
     *
     * @param  string  $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createConnection($driver)
    {

        $method = 'create'.Str::studly($driver).'Connection';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new InvalidArgumentException("Connection [$driver] not supported.");
    }

    /**
     * @return DropboxConnection
     */
    protected function createDropboxConnection()
    {
        return new DropboxConnection($this->driver('dropbox'));
    }
}