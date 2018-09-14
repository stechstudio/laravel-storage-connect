<?php

namespace STS\StorageConnect\Providers;

use Kunnu\Dropbox\DropboxApp;
use SocialiteProviders\Dropbox\Provider;
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
     * @return Dropbox
     */
    protected function makeService()
    {
        $service = new Dropbox(
            new DropboxApp($this->fullConfig['client_id'], $this->fullConfig['client_secret']),
            ['random_string_generator' => 'openssl']
        );

        $service->setAccessToken($this->token);

        return $service;
    }
}