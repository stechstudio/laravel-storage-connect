<?php

namespace STS\StorageConnect\Drivers;

use Illuminate\Session\Store;
use SocialiteProviders\Manager\OAuth2\User;
use StorageConnect;

trait OAuthBehavior
{
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
     */
    public function __construct(array $config)
    {
        parent::__construct(
            app('request'), $config['client_id'],
            $config['client_secret'], $this->callbackUrl($config, app('request')),
            array_get($config, 'guzzle', [])
        );
    }

    /**
     * @return string
     */
    protected function callbackUrl($config, $request)
    {
        return sprintf("https://%s/%s/callback/%s",
            array_get($config, 'callback_domain') ?: config('storage-connect.callback_domain') ?: $request->getHost(),
            config('storage-connect.path'),
            $this->name()
        );
    }

    /**
     * @return Store
     */
    public function session()
    {
        return $this->request->session();
    }

    /**
     * @return string
     */
    protected function getState()
    {
        return base64_encode(json_encode(array_merge(
            ['csrf' => str_random(40)],
            StorageConnect::getState()
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
}