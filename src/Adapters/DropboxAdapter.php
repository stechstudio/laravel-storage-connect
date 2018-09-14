<?php

namespace STS\StorageConnect\Adapters;

use Kunnu\Dropbox\Exceptions\DropboxClientException;
use STS\StorageConnect\Exceptions\UploadException;

class DropboxAdapter extends Adapter
{
    /**
     * @var string
     */
    protected $name = "dropbox";

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
     * @return array
     */
    public function getQuota()
    {
        $usage = $this->provider->getSpaceUsage();

        $totalSpace = array_get($usage, "allocation.allocated", 0);
        $spaceUsed = array_get($usage, "used", 0);

        return [
            'total_space'     => $totalSpace,
            'space_used'      => $spaceUsed,
            'space_available' => $totalSpace - $spaceUsed,
            'percent_full'    => $totalSpace > 0
                ? round(($spaceUsed / $totalSpace) * 100, 1)
                : 0
        ];
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
                return $this->provider->saveUrl($destinationPath, $sourcePath);
            }

            return $this->provider->upload($sourcePath, $destinationPath, [
                'mode' => 'overwrite'
            ]);
        } catch (DropboxClientException $e) {
            throw $this->handleException($e, new UploadException($sourcePath, $e));
        }
    }

    /**
     * @param DropboxClientException $e
     * @param UploadException $uploadException
     *
     * @return UploadException
     */
    protected function handleException(DropboxClientException $e, UploadException $uploadException)
    {
        // First check for connection failure
        if(str_contains($e->getMessage(), "Connection timed out")) {
            return $uploadException->setRetry("Connection timeout");
        }

        // See if we have a parseable error
        $error = json_decode($e->getMessage(), true);

        if(!is_array($error)) {
            return $uploadException->setRetry("Unknown error uploading file to Dropbox: " . $e->getMessage());
        }

        if(str_contains(array_get($error, 'error_summary'), "insufficient_space")) {
            return $uploadException->setDisable("Dropbox account is full", self::STORAGE_FULL);
        }

        if(str_contains(array_get($error, 'error_summary'), "invalid_access_token")) {
            return $uploadException->setDisable("Dropbox integration is invalid", self::INVALID_ACCESS_TOKEN);
        }

        if(str_contains(array_get($error, 'error_summary'), 'too_many_write_operations')) {
            return $uploadException->setRetry("Hit rate limit");
        }

        return $uploadException->setRetry("Unknown Dropbox exception: " . $e->getMessage());
    }
}