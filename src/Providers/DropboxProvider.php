<?php

namespace STS\StorageConnect\Providers;

use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Models\FileMetadata;
use SocialiteProviders\Dropbox\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\AbstractConnection;
use STS\StorageConnect\Connections\DropboxConnection;
use STS\StorageConnect\Traits\EnhancedProvider;
use Kunnu\Dropbox\Dropbox as DropboxService;

class DropboxProvider extends Provider implements ProviderContract
{
    use EnhancedProvider;

    /**
     * @var DropboxService
     */
    protected $service;

    /**
     * @param User $user
     *
     * @return AbstractConnection
     */
    protected function mapUserToConnection( User $user )
    {
        return (new DropboxConnection($this))->load([
            'status' => 'active',
            'name'   => $user->user['name']['display_name'],
            'email'  => $user->user['email'],
            'token'  => $user->accessTokenResponseBody
        ]);
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return FileMetadata|string
     */
    public function upload( $sourcePath, $destinationPath )
    {
        if(starts_with($sourcePath, "http")) {
            return $this->service()->saveUrl($destinationPath, $sourcePath);
        }

        return $this->service()->upload($sourcePath, str_start($destinationPath, '/'), [
            'mode' => 'overwrite'
        ]);
    }

    /**
     * @return DropboxService
     */
    protected function makeService()
    {
        $service = new DropboxService(
            new DropboxApp($this->fullConfig['client_id'], $this->fullConfig['client_secret']),
            ['random_string_generator' => 'openssl']
        );

        $service->setAccessToken($this->connection->token['access_token']);

        return $service;
    }
}