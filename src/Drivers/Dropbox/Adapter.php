<?php

namespace STS\StorageConnect\Drivers\Dropbox;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Models\FileMetadata;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\Models\Quota;
use STS\StorageConnect\UploadRequest;
use STS\StorageConnect\UploadResponse;

/**
 * @method Dropbox service()
 */
class Adapter extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $driver = "dropbox";

    /**
     * @var string
     */
    protected $providerClass = Provider::class;

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
     * @return \STS\StorageConnect\Models\Quota
     */
    public function getQuota()
    {
        $usage = $this->service()->getSpaceUsage();

        return new Quota(Arr::get($usage, "allocation.allocated", 0), Arr::get($usage, "used", 0));
    }

    /**
     * @param UploadRequest $request
     *
     * @return UploadResponse
     *
     */
    public function upload(UploadRequest $request)
    {
        try {
            if (Str::startsWith($request->getSourcePath(), "http")) {
                return new UploadResponse($request, $this->service()->saveUrl($request->getDestinationPath(), $request->getSourcePath()), true);
            }

            return new UploadResponse($request, $this->service()->upload(new File($request->getSourcePath()), $request->getDestinationPath(), [
                'mode' => 'overwrite'
            ]));
        } catch (DropboxClientException $e) {
            throw $this->handleUploadException($e, new UploadException($request, $e));
        }
    }

    /**
     * @param DropboxClientException $dropbox
     * @param UploadException $upload
     *
     * @return UploadException
     */
    protected function handleUploadException(DropboxClientException $dropbox, UploadException $upload)
    {
        // First check for connection failure
        if (Str::contains($dropbox->getMessage(), "Connection timed out")) {
            return $upload->retry("Connection timeout");
        }

        // Other known errors
        if (Str::contains($dropbox->getMessage(), "Async Job ID cannot be null")) {
            return $upload->message("Invalid upload job ID");
        }

        // See if we have a parseable error
        $error = json_decode($dropbox->getMessage(), true);

        if (!is_array($error)) {
            return $upload->retry("Unknown error uploading file to Dropbox: " . $dropbox->getMessage());
        }

        if (Str::contains(Arr::get($error, 'error_summary'), "insufficient_space")) {
            return $upload->disable("Dropbox account is full", CloudStorage::SPACE_FULL);
        }

        if (Str::contains(Arr::get($error, 'error_summary'), "invalid_access_token")) {
            return $upload->disable("Dropbox integration is invalid", CloudStorage::INVALID_TOKEN);
        }

        if (Str::contains(Arr::get($error, 'error_summary'), 'too_many_write_operations')) {
            return $upload->retry("Hit rate limit");
        }

        return $upload->retry("Unknown Dropbox exception: " . $dropbox->getMessage());
    }

    /**
     * @param UploadResponse $response
     *
     * @return UploadResponse
     */
    public function checkUploadStatus(UploadResponse $response)
    {
        try {
            $result = $this->service()->checkJobStatus($response->getOriginal());
        } catch (DropboxClientException $e) {
            throw $this->handleUploadException($e, new UploadException($response->getRequest(), $e));
        }

        if ($result instanceof FileMetadata) {
            return new UploadResponse($response->getRequest(), $result);
        }

        if ($result == "in_progress") {
            return $response;
        }

        // At this point we seem to have an unexpected result from Dropbox. If we have tried at least
        // 10 times, I think it's worth just failing at this point.
        if ($response->getStatusChecks() > 10) {
            throw new UploadException($response->getRequest(), null, 'Unexpected response from Dropbox when checking on async job: ' . $result);
        }

        // Ok then, we'll keep retrying
        return $response;
    }

    /**
     * @param string $remotePath
     *
     * @return bool
     */
    public function pathExists($remotePath)
    {
        try {
            return $this->service()->getMetadata($remotePath) instanceof FileMetadata;
        } catch(DropboxClientException $e) {
            return false;
        }
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

        $service->setAccessToken(Arr::get($this->token, "access_token"));

        return $service;
    }
}