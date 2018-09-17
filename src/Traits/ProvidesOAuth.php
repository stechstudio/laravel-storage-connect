<?php

namespace STS\StorageConnect\Traits;

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use SocialiteProviders\Manager\OAuth2\User;

/**
 * Class ProvidesOAuth
 * @package STS\StorageConnect\Providers\Traits
 */
trait ProvidesOAuth
{
    /**
     * @var array
     */
    protected $state;

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
     * @param       $request
     * @param       $state
     */
    public function __construct( array $config, $request, $state )
    {
        $this->state = (array)$state;

        parent::__construct(
            $request, $config['client_id'],
            $config['client_secret'], $this->callbackUrl($config, $request),
            array_get($config, 'guzzle', [])
        );
    }

    /**
     * @return string
     */
    protected function callbackUrl( $config, $request )
    {
        return sprintf("https://%s/%s/callback/%s",
            array_get($config, 'callback_domain',
                array_get(config('storage-connect'), 'callback_domain',
                    $request->getHost()
                )
            ),
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
            $this->state
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