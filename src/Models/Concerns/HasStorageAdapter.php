<?php
namespace STS\StorageConnect\Models\Concerns;

use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\StorageConnectFacade;

trait HasStorageAdapter
{
    /**
     * @var
     */
    protected $adapter;

    /**
     * @param null $redirectUrl
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authorize($redirectUrl = null)
    {
        return $this->adapter()->authorize($this, $redirectUrl);
    }

    /**
     * @return AbstractAdapter
     */
    public function adapter()
    {
        if (!$this->adapter) {
            $this->adapter = StorageConnectFacade::adapter($this->driver)->setToken((array)$this->token, function ($token) {
                $this->update(['token' => $token]);
            });
        }

        return $this->adapter;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $this->adapter = null;

        return array_keys(get_object_vars($this));
    }
}