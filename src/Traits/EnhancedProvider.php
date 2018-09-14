<?php

namespace STS\StorageConnect\Traits;

use Illuminate\Http\Request;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\StorageConnectManager;

/**
 * Class EnhancedProvider
 * @package STS\StorageConnect\Providers\Traits
 */
trait EnhancedProvider
{
    /**
     * @var array
     */
    protected $fullConfig;

    /**
     * @var StorageConnectManager
     */
    protected $manager;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $token;

    /**
     * EnhancedProvider constructor.
     *
     * @param array $config
     * @param StorageConnectManager $manager
     * @param                       $app
     */
    public function __construct(array $config, StorageConnectManager $manager, $app)
    {
        $this->fullConfig = $config;
        $this->manager = $manager;

        parent::__construct(
            $app['request'], $config['client_id'],
            $config['client_secret'], $this->callbackUrl($app['request']),
            array_get($config, 'guzzle', [])
        );
    }

    /**
     * @return string
     */
    protected function callbackUrl($request)
    {
        return sprintf("https://%s/%s/callback/%s",
            config('storage-connect.callback_domain',
                array_get($this->fullConfig, 'callback_domain', $request->getHost())
            ),
            config('storage-connect.path'),
            $this->name()
        );
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    protected function getState()
    {
        return base64_encode(json_encode(array_merge(
            ['csrf' => str_random(40)],
            (array)$this->manager->getCustomState()
        )));
    }

    /**
     * @return User
     */
    public function user()
    {
        if (!$this->user) {
            $this->user = parent::user();
        }

        return $this->user;
    }

    /**
     * @return string
     */
    public function name()
    {
        return strtolower(self::IDENTIFIER);
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
}