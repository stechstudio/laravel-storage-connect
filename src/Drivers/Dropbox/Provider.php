<?php

namespace STS\StorageConnect\Drivers\Dropbox;

use SocialiteProviders\Dropbox\Provider as BaseProvider;
use STS\StorageConnect\Drivers\OAuthBehavior;

class Provider extends BaseProvider
{
    use OAuthBehavior;
}