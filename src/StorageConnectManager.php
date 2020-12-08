<?php

namespace STS\StorageConnect;

use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Laravel\Socialite\Two\AbstractProvider;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\Models\CustomManagedCloudStorage;
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
    protected $state = [];

    /**
     * @var callable
     */
    protected $saveCallback;

    /**
     * @var callable
     */
    protected $loadCallback;

    /**
     * @var string
     */
    public static $appName = null;

    /**
     * @var array
     */
    protected $registered = [];

    /**
     * @param $driver
     *
     * @return AbstractAdapter
     */
    public function adapter($driver)
    {
        $this->verifyDriver($driver);

        return $this->container->make("sts.storage-connect.adapter.$driver");
    }

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['storage-connect.default'];
    }

    /**
     * @param $driver
     *
     * @return bool
     */
    public function isSupportedDriver($driver)
    {
        return in_array($driver, $this->registered)
            && in_array($driver, $this->container['config']['storage-connect.enabled'])
            && is_array($this->container['config']["services.$driver"])
            && $this->container['config']["services.$driver.client_id"] != null
            && $this->container['config']["services.$driver.client_secret"] != null;
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
        $this->verifyDriver($driver);

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
        $this->state = array_merge($this->state, $state);
    }

    /**
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param $driver
     *
     * @return RedirectResponse
     */
    public function finish($driver)
    {
        $props = (array) $this->container['request']->session()->pull('storage-connect');

        $storage = Arr::get($props, 'custom') == true
            ? $this->driver($driver)
            : CloudStorage::findOrFail(Arr::get($props, 'id'));

        $this->adapter($driver)->finish($storage);

        return $this->redirectAfterConnect(Arr::get($props, 'redirect'));
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
                ? $this->container['config']->get('storage-connect.redirect_after_connect')
                : $redirectUrl
        );
    }

    /**
     * @return string
     */
    public function appName()
    {
        if ($appName = self::appName) {
            return $appName;
        }

        if ($appName = $this->container['config']->get('storage-connect.app_name')) {
            return $appName;
        }

        return $this->container['config']->get('app.name');
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
     * @param $name
     * @param $abstractClass
     * @param $providerClass
     */
    public function register($name, $abstractClass, $providerClass)
    {
        $this->container->bind($abstractClass, function($app) use($abstractClass, $name) {
            return new $abstractClass($app['config']->get("services.$name"));
        });
        $this->container->alias($abstractClass, "sts.storage-connect.adapter.$name");

        $this->container->bind($providerClass, function($app) use($providerClass, $name) {
            return new $providerClass($app['config']->get("services.$name"));
        });
        $this->container->alias($providerClass, "sts.storage-connect.provider.$name");

        $this->registered[] = $name;
    }
}