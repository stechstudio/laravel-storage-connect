<?php
namespace STS\StorageConnect\Adapters;

use Illuminate\Http\RedirectResponse;
use STS\StorageConnect\Events\CloudStorageSetup;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\StorageConnectManager;

abstract class Adapter
{
    /**
     * @var
     */
    protected $config;

    /**
     * @var StorageConnectManager
     */
    protected $manager;

    /**
     * @var mixed
     */
    protected $service;

    /**
     * @var array
     */
    protected $token;

    /**
     * @var callable
     */
    protected $tokenUpdateCallback;

    /**
     * DropboxAdapter constructor.
     *
     * @param array                 $config
     * @param StorageConnectManager $manager
     */
    public function __construct(array $config, StorageConnectManager $manager)
    {
        $this->config = $config;
        $this->manager = $manager;
    }

    /**
     * @param $token
     * @paran $callback
     *
     * @return $this
     */
    public function setToken($token, $callback)
    {
        $this->token = $token;
        $this->tokenUpdateCallback = $callback;

        return $this;
    }

    /**
     * @param $token
     */
    protected function updateToken($token)
    {
        call_user_func($this->tokenUpdateCallback, $token);
    }

    /**
     * @param $storage
     * @param null $redirectUrl
     *
     * @return RedirectResponse
     */
    public function authorize($storage, $redirectUrl = null)
    {
        if(!$storage->exists()) {
            $storage->save();
        }

        $this->provider()->session()->put('storage-connect.id', $storage->id);

        if($redirectUrl != null) {
            $this->provider()->session()->session()->put('storage-connect.redirect', $redirectUrl);
        }

        return $this->provider()->redirect();
    }

    /**
     * @return RedirectResponse
     */
    public function finish()
    {
        $props = $this->provider()->session()->pull('storage-connect');

        /** @var CloudStorage $storage */
        $storage = CloudStorage::findOrFail($props['id']);
        $storage->update(array_merge(
            $this->mapUserDetails($this->provider()->user()),
            [
                'token' => $this->provider()->user()->accessTokenResponseBody,
                'connected' => 1,
                'enabled' => 1
            ]
        ));
        $storage->checkSpace();

        event(new CloudStorageSetup($storage));

        return $this->manager->redirectAfterConnect(array_get($props, 'redirect'));
    }

    /**
     * @return string
     */
    public function driver()
    {
        return $this->driver;
    }

    /**
     * @return mixed
     */
    protected function provider()
    {
        return $this->manager->provider($this->driver());
    }

    /**
     * @return mixed
     */
    public function service()
    {
        if (!$this->service) {
            $this->service = $this->makeService();
        }

        return $this->service;
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->service()->$method(...$parameters);
    }

    abstract protected function makeService();

    abstract function getQuota();

    abstract protected function mapUserDetails($user);
}