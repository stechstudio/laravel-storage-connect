<?php

namespace STS\StorageConnect\Providers;

use Kunnu\Dropbox\DropboxApp;
use SocialiteProviders\Dropbox\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Providers\Traits\EnhancedProvider;
use Kunnu\Dropbox\Dropbox as DropboxService;

class Dropbox extends Provider implements ProviderContract
{
    use EnhancedProvider;

    /**
     * @var DropboxService
     */
    protected $service;

    /**
     * @param User $user
     *
     * @return $this
     */
    protected function mapUser( User $user )
    {
        $this->connection = [
            'status'       => 'active',
            'name'         => $user->user['name']['display_name'],
            'email'        => $user->user['email'],
            'access_token' => $user->accessTokenResponseBody
        ];

        return $this;
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
     * @return DropboxService
     */
    protected function service()
    {
        if (!$this->service) {
            $this->service = new DropboxService(
                new DropboxApp($this->config['client_id'], $this->config['client_secret']),
                ['random_string_generator' => 'openssl']
            );

            $this->service->setAccessToken($this->connection['access_token']['access_token']);
        }

        return $this->service;
    }
}