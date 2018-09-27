<?php
namespace STS\StorageConnect\Drivers\Dropbox;

use Kunnu\Dropbox\DropboxFile;

class File extends DropboxFile
{
    /**
     * File constructor.
     *
     * @param string $filePath
     * @param string $mode
     */
    public function __construct($filePath, $mode = self::MODE_READ)
    {
        parent::__construct($filePath, $mode = self::MODE_READ);

        if(starts_with($filePath, "s3://")) {
            $this->setStream($this->getSeekableS3Stream());
        }
    }

    /**
     * @return \GuzzleHttp\Psr7\Stream
     */
    protected function getSeekableS3Stream()
    {
        return \GuzzleHttp\Psr7\stream_for(fopen($this->path, $this->mode, false, stream_context_create([
            's3' => ['seekable' => true]
        ])));
    }
}