<?php

namespace STS\StorageConnect\Adapters;

use Google_Client;
use Google_Service_Drive;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Types\Quota;

class GoogleAdapter extends Adapter
{
    /**
     * @var string
     */
    protected $name = "google";

    /**
     * @param $user
     *
     * @return array
     */
    protected function mapUserDetails($user)
    {
        return [
            'name'  => $user->name,
            'email' => $user->email,
        ];
    }

    /**
     * @return Quota
     */
    public function getQuota()
    {
        $about = $this->service()->about->get();

        return new Quota($about->getQuotaBytesTotal(), $about->getQuotaBytesUsed());
    }

    /**
     * @return Google_Service_Drive
     */
    protected function makeService()
    {
        $client = new Google_Client([
            'client_id'     => $this->fullConfig['client_id'],
            'client_secret' => $this->fullConfig['client_secret']
        ]);

        $client->setApplicationName(
            $this->app['config']->get('storage-connect.app_name', $this->app['config']->get('app.name'))
        );

        $client->setAccessToken($this->token);

        if ($client->isAccessTokenExpired()) {
            $this->updateToken($client->refreshToken($client->getRefreshToken()));
        }

        return new Google_Service_Drive($client);
    }
}