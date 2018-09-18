<?php

namespace STS\StorageConnect\Drivers\Dropbox;

use SocialiteProviders\Dropbox\Provider as BaseProvider;
use STS\StorageConnect\Traits\ProvidesOAuth;

class Provider extends BaseProvider
{
    use ProvidesOAuth;
}