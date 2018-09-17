<?php

namespace STS\StorageConnect;

use Carbon\Carbon;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use STS\StorageConnect\Adapters\DropboxAdapter;
use STS\StorageConnect\Adapters\GoogleAdapter;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\Models\CustomManagedCloudStorage;
use STS\StorageConnect\Providers\DropboxProvider;
use STS\StorageConnect\Providers\GoogleProvider;
use UnexpectedValueException;

/**
 * Class StorageConnectManager
 * @package STS\StorageConnect
 */
class StorageConnectManager extends Manager
{
    /**
     * @var array
     */
    protected $includeState = [];

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
    protected $instances = [];

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
     * @param $driver
     *
     * @return bool
     */
    public function isSupportedDriver($driver)
    {
        return in_array($driver, $this->app['config']['storage-connect.enabled'])
            && is_array($this->app['config']["services.$driver"])
            && $this->app['config']["services.$driver.client_id"] != null
            && $this->app['config']["services.$driver.client_secret"] != null;
    }

    /**
     * @param $driver
     *
     * @return bool
     */
    public function verifyDriver($driver)
    {
        if(!$this->isSupportedDriver($driver)) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        return true;
    }

    /**
     * @param string $driver
     *
     * @return CloudStorage
     */
    protected function createDriver($driver)
    {
        $attributes = $this->load($driver);

        if (!is_array($attributes)) {
            $attributes = json_decode($attributes, true);
        }

        if (is_array($attributes) && array_key_exists('driver', $attributes)) {
            $instance = (new CustomManagedCloudStorage)->restore($attributes, $this->saveCallback);
        } else {
            $instance = CustomManagedCloudStorage::init($driver, $this->saveCallback);
        }

        return $instance;
    }

    /**
     * @param $callback
     */
    public function loadUsing($callback)
    {
        $this->loadCallback = $callback;
    }

    /**
     * @param $driver
     *
     * @return mixed
     */
    protected function load($driver)
    {
        if(!$this->loadCallback) {
            throw new UnexpectedValueException("No callback provided to load storage connection");
        }

        return call_user_func($this->loadCallback, $driver);
    }

    /**
     * @param $callback
     */
    public function saveUsing($callback)
    {
        $this->saveCallback = $callback;
    }

    /**
     * @param array $state
     */
    public function includeState(array $state)
    {
        $this->includeState = array_merge($this->includeState, $state);
    }

    /**
     * @param $driver
     *
     * @return RedirectResponse
     */
    public function finish($driver)
    {
        $props = (array) $this->app['request']->session()->pull('storage-connect');

        $storage = array_get($props, 'custom') == true
            ? $this->driver($driver)
            : CloudStorage::findOrFail(array_get($props, 'id'));

        $this->adapter($driver)->finish($storage);

        return $this->redirectAfterConnect(array_get($props, 'redirect'));
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
     * @param null $driver
     *
     * @return mixed
     */
    public function adapter($driver = null)
    {
        return $this->createTypes('adapter', $driver);
    }

    /**
     * @return DropboxAdapter
     */
    protected function createDropboxAdapter()
    {
        return new DropboxAdapter($this->app['config']['services.dropbox'], $this);
    }

    /**
     * @return GoogleAdapter
     */
    protected function createGoogleAdapter()
    {
        return new GoogleAdapter($this->app['config']['services.google'], $this);
    }

    /**
     * @param null $driver
     *
     * @return mixed
     */
    public function provider($driver = null)
    {
        return $this->createTypes('provider', $driver);
    }

    /**
     * @return DropboxProvider
     */
    protected function createDropboxProvider()
    {
        return new DropboxProvider($this->app['config']['services.dropbox'], $this->app['request'], $this->includeState);
    }

    /**
     * @return GoogleProvider
     */
    public function createGoogleProvider()
    {
        return new GoogleProvider($this->app['config']['services.google'], $this->app['request'], $this->includeState);
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
     * @return CloudStorage
     */
    public function instance()
    {
        return $this->driver();
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
        return $this->driver()->$method(...$parameters);
    }

    /**
     * @param $type
     * @param $driver
     *
     * @return mixed
     */
    protected function createTypes($type, $driver)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (!$this->isSupportedDriver($driver)) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        if (!isset($this->instances[$type][$driver])) {
            $method = 'create' . Str::studly($driver) . Str::studly($type);

            if (method_exists($this, $method)) {
                $this->instances[$type][$driver] = $this->$method();
            } else {
                throw new InvalidArgumentException(Str::studly($type) . " [$driver] not supported.");
            }
        }

        return $this->instances[$type][$driver];
    }
}