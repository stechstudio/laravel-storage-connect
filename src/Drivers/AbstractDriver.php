<?php
namespace STS\StorageConnect\Drivers;

use Session;
use STS\StorageConnect\StorageConnectManager;

/**
 * Class AbstractDriver
 * @package STS\StorageConnect\Drivers
 */
abstract class AbstractDriver
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var StorageConnectManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var
     */
    protected $csrf;

    /**
     * AbstractDriver constructor.
     *
     * @param array $config
     * @param StorageConnectManager $manager
     */
    public function __construct(array $config, StorageConnectManager $manager)
    {
        $this->config = $config;
        $this->manager = $manager;
    }

    /**
     * @return mixed
     */
    abstract public function getAuthUrl();

    /**
     * @return string
     */
    protected function callbackUrl()
    {
        return sprintf("https://%s/%s/callback/%s",
            config('storage-connect.callback_domain', app('request')->getHost()),
            config('storage-connect.route'),
            $this->name
        );
    }

    /**
     * @return string
     */
    protected function state()
    {
        Session::put('storage-connect-' . $this->name . '-csrf', $this->csrf());
        Session::save();

        return base64_encode(json_encode(array_merge(
            ['csrf' => $this->csrf()],
            $this->manager->getCustomState($this->name)
        )));
    }

    /**
     * @return string
     */
    protected function csrf()
    {
        if(!$this->csrf) {
            $this->csrf = str_random();
        }

        return $this->csrf;
    }

    /**
     * @param array $state
     *
     * @return bool
     * @throws \Exception
     */
    public function verifyCsrf(array $state)
    {
        if(array_get($state, 'csrf') == Session::get('storage-connect-' . $this->name . '-csrf')) {
            return true;
        }

        dd($state, Session::get('storage-connect-' . $this->name . '-csrf'));

        throw new \Exception("Invalid state returned");
    }

    /**
     * @return array
     */
    abstract public function getFinishedState();

    /**
     * @return array
     */
    abstract public function finish();
}