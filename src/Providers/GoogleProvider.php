<?php

namespace STS\StorageConnect\Providers;


use Google_Client;
use Google_Service_Drive;
use SocialiteProviders\Google\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\AbstractConnection;
use STS\StorageConnect\Connections\GoogleConnection;
use STS\StorageConnect\Traits\EnhancedProvider;

class GoogleProvider extends Provider implements ProviderContract
{
    use EnhancedProvider;

    /**
     * @var array
     */
    protected $scopes = [
        'profile', 'email', 'openid',
        'https://www.googleapis.com/auth/drive.file'
    ];

    /**
     * @var array
     */
    protected $parameters = [
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];

    /**
     * @var Google_Service_Drive
     */
    protected $service;

    /**
     * @param User $user
     *
     * @return AbstractConnection
     */
    protected function mapUserToConnection( User $user )
    {
        return (new GoogleConnection($this))->load([
            'status' => 'active',
            'name'   => $user->name,
            'email'  => $user->email,
            'token'  => $user->accessTokenResponseBody
        ]);
    }

    /**
     * @param $localPath
     * @param $remotePath
     */
    public function upload( $localPath, $remotePath )
    {
        $this->service()->upload($localPath, str_start($remotePath, '/'), [
            'mode' => 'overwrite'
        ]);
    }

    /**
     * @return Google_Service_Drive
     */
    public function service()
    {
        if (!$this->service) {
            $client = new Google_Client();
            $client->setApplicationName(
                $this->app['config']->get('storage-connect.app_name', $this->app['config']->get('app.name'))
            );
            $client->setAccessToken($this->connection->token);

            $this->service = new Google_Service_Drive($client);
        }

        return $this->service;
    }


}