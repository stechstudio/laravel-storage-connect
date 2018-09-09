<?php

namespace STS\StorageConnect\Connections;

use Exception;

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
     * @param Exception              $e
     * @param                        $sourcePath
     */
    protected function handleUploadError( Exception $e, $sourcePath )
    {

    }
}