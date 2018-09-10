<?php
namespace STS\StorageConnect\Connections;

use Exception;
use Queue;
use Log;

/**
 * Class DropboxConnection
 * @package STS\StorageConnect\Connections
 */
class DropboxConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = "dropbox";

    /**
     * @param Exception $e
     * @param                        $sourcePath
     */
    protected function handleUploadError( Exception $e, $sourcePath)
    {
        // First check for connection failure
        if(str_contains($e->getMessage(), "Connection timed out")) {
            return $this->retry("Connection timeout", $sourcePath);
        }

        // See if we have a parseable error
        $error = json_decode($e->getMessage(), true);

        if(!is_array($error)) {
            return $this->retry("Unknown error pushing EFS file to Dropbox: " . $e->getMessage(), $sourcePath);
        }

        if(str_contains(array_get($error, 'error_summary'), "insufficient_space")) {
            return $this->disable("Dropbox account is full", "full");
        }

        if(str_contains(array_get($error, 'error_summary'), "invalid_access_token")) {
            return $this->disable("Dropbox integration is invalid", "invalid");
        }

        if(str_contains(array_get($error, 'error_summary'), 'too_many_write_operations')) {
            return $this->retry("Hit rate limit", $sourcePath);
        }

        $this->retry("Unknown Dropbox exception: " . $e->getMessage(), $sourcePath);
    }
}