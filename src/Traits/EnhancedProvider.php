<?php

namespace STS\StorageConnect\Traits;

use Illuminate\Http\RedirectResponse;
use SocialiteProviders\Manager\OAuth2\User;
use StorageConnect;
use STS\StorageConnect\Connections\AbstractConnection;
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
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var AbstractConnection
     */
    protected $connection;

    /**
     * EnhancedProvider constructor.
     *
     * @param array                 $config
     * @param StorageConnectManager $manager
     * @param                       $app
     */
    public function __construct( array $config, StorageConnectManager $manager, $app )
    {
        $this->fullConfig = $config;
        $this->manager = $manager;
        $this->app = $app;

        parent::__construct(
            $this->app['request'], $config['client_id'],
            $config['client_secret'], $this->callbackUrl(),
            array_get($config, 'guzzle', [])
        );
    }

    /**
     * @return string
     */
    protected function callbackUrl()
    {
        return sprintf("https://%s/%s/callback/%s",
            $this->app['config']->get('storage-connect.callback_domain',
                array_get($this->fullConfig, 'callback_domain',
                    $this->app['request']->getHost()
                )
            ),
            $this->app['config']->get('storage-connect.path'),
            $this->name()
        );
    }

    /**
     * @return string
     */
    protected function getState()
    {
        return base64_encode(json_encode(array_merge(
            ['csrf' => str_random(40)],
            (array) $this->manager->includeState
        )));
    }

    /**
     * @return RedirectResponse
     */
    public function finish()
    {
        $this->connection = $this->mapUserToConnection($this->user());

        return $this->manager->saveConnectedStorage($this->connection, $this->name());
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
     * @param AbstractConnection $connection
     *
     * @return $this
     */
    public function load( AbstractConnection $connection )
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return AbstractConnection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @param AbstractConnection $connection
     *
     * @return $this
     */
    public function setConnection(AbstractConnection $connection)
    {
        $this->connection = $connection;

        return $this;
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
     * @param AbstractConnection $connection
     * @param $redirectUrl
     *
     * @return mixed
     */
    public function setup(AbstractConnection $connection, $redirectUrl)
    {
        $this->request->session()->put('storage-connect.connection', $connection);
        $this->request->session()->put('storage-connect.redirect', $redirectUrl);

        return $this->redirect();
    }
}