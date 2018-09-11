<?php

namespace STS\StorageConnect\Providers;

use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Models\FileMetadata;
use SocialiteProviders\Dropbox\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\DropboxConnection;
use STS\StorageConnect\Traits\EnhancedProvider;
use Kunnu\Dropbox\Dropbox;

class DropboxProvider extends Provider implements ProviderContract
{
    use EnhancedProvider;

    /**
     * @var Dropbox
     */
    protected $service;

    /**
     * @var string
     */
    protected $connectionClass = DropboxConnection::class;

    /**
     * @param User $user
     *
     * @return array
     */
    protected function mapUserToConnectionConfig( User $user )
    {
        return [
            'name'   => $user->user['name']['display_name'],
            'email'  => $user->user['email'],
            'token'  => $user->accessTokenResponseBody
        ];
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return FileMetadata|string
     */
    public function upload( $sourcePath, $destinationPath )
    {
        $destinationPath = str_start($destinationPath, '/');

        if(starts_with($sourcePath, "http")) {
            return $this->service()->saveUrl($destinationPath, $sourcePath);
        }

        return $this->service()->upload($sourcePath, $destinationPath, [
            'mode' => 'overwrite'
        ]);
    }

    /**
     * @return float
     */
    public function percentFull()
    {
        $usage = $this->service()->getSpaceUsage();

        return round(($usage['used'] / $usage['allocation']['allocated']) * 100, 1);
    }

    /**
     * @return Dropbox
     */
    protected function makeService()
    {
        $service = new Dropbox(
            new DropboxApp($this->fullConfig['client_id'], $this->fullConfig['client_secret']),
            ['random_string_generator' => 'openssl']
        );

        $service->setAccessToken($this->connection->token['access_token']);

        return $service;
    }
}