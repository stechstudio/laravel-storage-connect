<?php

namespace STS\StorageConnect\Drivers\Google;

use SocialiteProviders\Google\Provider as BaseProvider;
use STS\StorageConnect\Drivers\OAuthBehavior;

/**
 * Class GoogleProvider
 * @package STS\StorageConnect\Providers
 */
class Provider extends BaseProvider
{
    use OAuthBehavior;

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
        'prompt'      => 'consent'
    ];
}