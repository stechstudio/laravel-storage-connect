<?php
namespace STS\StorageConnect\Connections;

use Exception;
use Queue;
use Log;

/**
 * Class DropboxConnection
 * @package STS\StorageConnect\Connections
 */
class DropboxConnection extends Connection
{
    /**
     * @var string
     */
    protected $name = "dropbox";

    /**
     * @param Exception $e
     * @param                        $sourcePath
     *
     * @return DropboxConnection|void
     */
    protected function handleUploadError( Exception $e, $sourcePath)
    {
        // First check for connection failure
        if(str_contains($e->getMessage(), "Connection timed out")) {
            return $this->retry("Connection timeout", $e, $sourcePath);
        }

        // See if we have a parseable error
        $error = json_decode($e->getMessage(), true);

        if(!is_array($error)) {
            return $this->retry("Unknown error uploading file to Dropbox: " . $e->getMessage(), $e, $sourcePath);
        }

        if(str_contains(array_get($error, 'error_summary'), "insufficient_space")) {
            return $this->disable("Dropbox account is full", self::STORAGE_FULL);
        }

        if(str_contains(array_get($error, 'error_summary'), "invalid_access_token")) {
            return $this->disable("Dropbox integration is invalid", self::INVALID_ACCESS_TOKEN);
        }

        if(str_contains(array_get($error, 'error_summary'), 'too_many_write_operations')) {
            return $this->retry("Hit rate limit", $e, $sourcePath);
        }

        $this->retry("Unknown Dropbox exception: " . $e->getMessage(), $e, $sourcePath);
    }
}