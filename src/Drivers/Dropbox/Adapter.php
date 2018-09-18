<?php

namespace STS\StorageConnect\Drivers\Dropbox;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\Types\Quota;

class Adapter extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $driver = "dropbox";

    /**
     * @param $user
     *
     * @return array
     */
    protected function mapUserDetails($user)
    {
        return [
            'name'  => $user->user['name']['display_name'],
            'email' => $user->user['email']
        ];
    }

    /**
     * @return Quota
     */
    public function getQuota()
    {
        $usage = $this->service()->getSpaceUsage();

        return new Quota(array_get($usage, "allocation.allocated", 0), array_get($usage, "used", 0));
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return mixed
     * @throws UploadException
     */
    public function upload($sourcePath, $destinationPath)
    {
        $destinationPath = str_start($destinationPath, '/');

        try {
            if (starts_with($sourcePath, "http")) {
                return $this->service()->saveUrl($destinationPath, $sourcePath);
            }

            return $this->service()->upload($sourcePath, $destinationPath, [
                'mode' => 'overwrite'
            ]);
        } catch (DropboxClientException $e) {
            throw $this->handleException($e, new UploadException($sourcePath, $e));
        }
    }

    /**
     * @param DropboxClientException $dropbox
     * @param UploadException $upload
     *
     * @return UploadException
     */
    protected function handleException(DropboxClientException $dropbox, UploadException $upload)
    {
        // First check for connection failure
        if (str_contains($dropbox->getMessage(), "Connection timed out")) {
            return $upload->retry("Connection timeout");
        }

        // See if we have a parseable error
        $error = json_decode($dropbox->getMessage(), true);

        if (!is_array($error)) {
            return $upload->retry("Unknown error uploading file to Dropbox: " . $dropbox->getMessage());
        }

        if (str_contains(array_get($error, 'error_summary'), "insufficient_space")) {
            return $upload->disable("Dropbox account is full", CloudStorage::SPACE_FULL);
        }

        if (str_contains(array_get($error, 'error_summary'), "invalid_access_token")) {
            return $upload->disable("Dropbox integration is invalid", CloudStorage::INVALID_TOKEN);
        }

        if (str_contains(array_get($error, 'error_summary'), 'too_many_write_operations')) {
            return $upload->retry("Hit rate limit");
        }

        return $upload->retry("Unknown Dropbox exception: " . $dropbox->getMessage());
    }

    /**
     * @return \SocialiteProviders\Manager\OAuth2\AbstractProvider
     */
    protected function makeProvider()
    {
        return new Provider($this->config);
    }

    /**
     * @return Dropbox
     */
    protected function makeService()
    {
        $service = new Dropbox(
            new DropboxApp($this->config['client_id'], $this->config['client_secret']),
            ['random_string_generator' => 'openssl']
        );

        $service->setAccessToken(array_get($this->token, "access_token"));

        return $service;
    }
}