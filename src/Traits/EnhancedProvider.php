<?php

namespace STS\StorageConnect\Traits;

use Illuminate\Http\RedirectResponse;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\Connection;
use STS\StorageConnect\Events\ConnectionEstablished;
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
     * @var Connection
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
     * @param Connection $connection
     * @param $redirectUrl
     *
     * @return mixed
     */
    public function authorize($redirectUrl = null, $connection = null)
    {
        if($connection instanceof Connection && $connection->owner()) {
            $this->request->session()->put('storage-connect.owner', $connection->owner());
        }

        if($redirectUrl != null) {
            $this->request->session()->put('storage-connect.redirect', $redirectUrl);
        }

        return $this->redirect();
    }

    /**
     * @return string
     */
    protected function getState()
    {
        return base64_encode(json_encode(array_merge(
            ['csrf' => str_random(40)],
            (array) $this->manager->getCustomState()
        )));
    }

    /**
     * @return RedirectResponse
     */
    public function finish()
    {
        $this->connection->initialize($this->mapUserToConnectionConfig($this->user()));

        $settings = $this->request->session()->pull('storage-connect');

        if(array_has($settings, 'owner')) {
            $this->connection->belongsTo(array_get($settings, 'owner'));
        }

        $this->connection->save();
        event(new ConnectionEstablished($this->connection, $this->name()));

        return $this->manager->redirectAfterConnect(array_get($settings, 'redirect'));
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
     * @param Connection $connection
     *
     * @return $this
     */
    public function load(Connection $connection )
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @param Connection $connection
     *
     * @return $this
     */
    public function setConnection(Connection $connection)
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
}