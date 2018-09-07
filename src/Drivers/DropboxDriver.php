<?php
namespace STS\StorageConnect\Drivers;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Request;

/**
 * Class DropboxDriver
 * @package STS\StorageConnect\Drivers
 */
class DropboxDriver extends AbstractDriver
{
    /**
     * @var DropboxApp
     */
    protected $app;

    /**
     * @var
     */
    protected $name = "dropbox";

    /**
     * @var array
     */
    protected $serviceConfig = [
        'random_string_generator' => 'openssl'
    ];

    /**
     * @var Dropbox
     */
    protected $service;

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->service()->getAuthHelper()->getOAuth2Client()->getAuthorizationUrl($this->callbackUrl(), $this->state());
    }

    /**
     * @return Dropbox
     */
    protected function service()
    {
        if(!$this->service) {
            $this->service = new Dropbox($this->app(), $this->serviceConfig);
        }

        return $this->service;
    }

    /**
     * @return DropboxApp
     */
    protected function app()
    {
        if(!$this->app) {
            $this->app = new DropboxApp($this->config['key'], $this->config['secret']);
        }

        return $this->app;
    }

    /**
     * @return array
     */
    public function getFinishedState()
    {
        return (array) json_decode(base64_decode(Request::input('state')), true);
    }

    /**
     * @return array
     */
    public function finish()
    {
        return [
            'status' => 'active',
            'token' => $this->service()->getAuthHelper()->getOAuth2Client()->getAccessToken(Request::input('code'), $this->callbackUrl())
        ];
    }
}