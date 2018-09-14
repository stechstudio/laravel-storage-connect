<?php
namespace STS\StorageConnect\Adapters;

use Illuminate\Http\RedirectResponse;
use STS\StorageConnect\Events\CloudStorageSetup;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\StorageConnectManager;

abstract class Adapter
{
    /**
     * @var StorageConnectManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $token;

    /**
     * DropboxAdapter constructor.
     *
     * @param StorageConnectManager $manager
     */
    public function __construct(StorageConnectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
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

        $this->provider()->request()->session()->put('storage-connect.id', $storage->id);

        if($redirectUrl != null) {
            $this->provider()->request()->session()->session()->put('storage-connect.redirect', $redirectUrl);
        }

        return $this->provider()->redirect();
    }

    /**
     * @return RedirectResponse
     */
    public function finish()
    {
        $props = $this->provider()->request()->session()->pull('storage-connect');

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
     * @return mixed
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    protected function provider()
    {
        return $this->manager->driver($this->name());
    }

    abstract function getQuota();

    abstract protected function mapUserDetails($user);
}