<?php
namespace STS\StorageConnect\Connections;

use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Queue;
use Log;
use STS\StorageConnect\Jobs\UploadFile;

/**
 * Class GoogleConnection
 * @package STS\StorageConnect\Connections
 */
class GoogleConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = "google";

    /**
     * @param      $localPath
     * @param      $remotePath
     * @param bool $queued
     *
     * @return bool
     */
    public function upload( $localPath, $remotePath, $queued = true)
    {
        if($queued) {
            return Queue::push(new UploadFile($localPath, $remotePath, $this));
        }

        try {
            $this->provider->upload($localPath, $remotePath);
            return true;
        } catch(DropboxClientException $e) {
            $this->handleUploadError($e, $localPath);
        }
    }

    /**
     * @param DropboxClientException $e
     * @param                        $localPath
     */
    protected function handleUploadError( DropboxClientException $e, $localPath)
    {
        // First check for connection failure
        if(str_contains($e->getMessage(), "Connection timed out")) {
            return $this->retry("Connection timeout", $localPath);
        }

        // See if we have a parseable error
        $error = json_decode($e->getMessage(), true);

        if(!is_array($error)) {
            return $this->retry("Unknown error pushing EFS file to Dropbox: " . $e->getMessage(), $localPath);
        }

        if(str_contains(array_get($error, 'error_summary'), "insufficient_space")) {
            return $this->disable("Dropbox account is full, disabling", "full");
        }

        if(str_contains(array_get($error, 'error_summary'), "invalid_access_token")) {
            return $this->disable("Dropbox integration is invalid, disabling", "invalid");
        }

        if(str_contains(array_get($error, 'error_summary'), 'too_many_write_operations')) {
            return $this->retry("Getting rate limited", $localPath);
        }

        $this->retry("Unknown Dropbox exception: " . $e->getMessage(), $localPath);
    }
}