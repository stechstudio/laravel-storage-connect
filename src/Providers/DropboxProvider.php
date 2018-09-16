<?php

namespace STS\StorageConnect\Providers;

use Kunnu\Dropbox\DropboxApp;
use SocialiteProviders\Dropbox\Provider;
use STS\StorageConnect\Traits\ProvidesOAuth;
use Kunnu\Dropbox\Dropbox;

class DropboxProvider extends Provider implements ProviderContract
{
    use ProvidesOAuth;
}