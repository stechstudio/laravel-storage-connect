<?php

namespace STS\StorageConnect;

use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\Connection;
use STS\StorageConnect\Connections\DropboxConnection;
use STS\StorageConnect\Connections\GoogleConnection;
use STS\StorageConnect\Drivers\DropboxDriver;
use STS\StorageConnect\Events\ConnectionEstablished;
use STS\StorageConnect\Exceptions\UnauthorizedException;
use STS\StorageConnect\Providers\DropboxProvider;
use STS\StorageConnect\Providers\GoogleProvider;

/**
 * Class StorageConnectManager
 * @package STS\StorageConnect
 */
class StorageConnectManager extends Manager
{
    /**
     * @var array
     */
    public static $includeState = [];

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
    protected $beforeAuthorizeCallback;

    /**
     * @var array
     */
    public $connections = [];

    /**
     * @var string
     */
    public static $appName = null;

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
     * @return GoogleProvider
     */
    public function createGoogleDriver()
    {
        return new GoogleProvider($this->app['config']['services.google'], $this, $this->app);
    }

    /**
     * @param $driver
     *
     * @return bool
     */
    public function isSupportedDriver($driver)
    {
        return in_array($driver, $this->app['config']['storage-connect.enabled'])
            && is_array($this->app['config']["services.$driver"]);
    }

    /**
     * @param string $driver
     *
     * @return mixed
     */
    protected function createDriver($driver)
    {
        if (!$this->isSupportedDriver($driver)) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        return parent::createDriver($driver);
    }

    /**
     * @return array
     */
    public function getCustomState()
    {
        return self::$includeState;
    }

    /**
     * @param $callback
     */
    public function saveUsing($callback)
    {
        $this->saveCallback = $callback;
    }

    /**
     * @param Connection $connection
     * @param $driver
     *
     * @return mixed
     */
    public function save(Connection $connection, $driver)
    {
        call_user_func_array($this->saveCallback, [$connection, $driver]);
    }

    public function beforeAuthorize($callback)
    {
        $this->beforeAuthorizeCallback = $callback;
    }

    /**
     * @param $driver
     *
     * @return bool|mixed
     * @throws UnauthorizedException
     */
    public function runBeforeAuthorize($driver)
    {
        if(!$this->beforeAuthorizeCallback) {
            return true;
        }

        $response = call_user_func($this->beforeAuthorizeCallback, $driver);

        if($response instanceof RedirectResponse) {
            return $response;
        }

        if($response === false) {
            throw new UnauthorizedException();
        }

        return true;
    }

    /**
     * @param $redirectUrl
     *
     * @return RedirectResponse
     */
    public function redirectAfterConnect($redirectUrl = null)
    {
        return new RedirectResponse(
            $redirectUrl == null
                ? $this->app['config']->get('storage-connect.redirect_after_connect')
                : $redirectUrl
        );
    }

    /**
     * @param $callback
     */
    public function loadUsing($callback)
    {
        $this->loadCallback = $callback;
    }

    /**
     * Get a connection instance.
     *
     * @param  string $driver
     * @param bool $load
     *
     * @return mixed
     */
    public function connection($driver = null, $load = true)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (!isset($this->connections[$driver])) {
            $this->connections[$driver] = $this->createConnection($driver);

            if ($load && $this->loadCallback) {
                $this->connections[$driver]->load(call_user_func($this->loadCallback, $driver));
            }
        }

        return $this->connections[$driver];
    }

    /**
     * Create a new connection instance.
     *
     * @param  string $driver
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createConnection($driver)
    {

        $method = 'create' . Str::studly($driver) . 'Connection';

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

    /**
     * @return GoogleConnection
     */
    protected function createGoogleConnection()
    {
        return new GoogleConnection($this->driver('google'));
    }

    /**
     * @return string
     */
    public function appName()
    {
        if ($appName = self::appName) {
            return $appName;
        }

        if ($appName = $this->app['config']->get('storage-connect.app_name')) {
            return $appName;
        }

        return $this->app['config']->get('app.name');
    }

    /**
     * Dynamically call the default connection instance.
     *
     * @param  string $method
     * @param  array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}