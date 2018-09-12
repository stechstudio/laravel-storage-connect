<?php
namespace STS\StorageConnect\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use STS\StorageConnect\Connections\Connection;

/**
 * Class UploadFile
 * @package STS\StorageConnect\Jobs
 */
class UploadFile implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var string
     */
    protected $sourcePath;
    /**
     * @var string
     */
    protected $remotePath;
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var int
     */
    public $tries = 5;

    /**
     * @var int
     */
    public $timeout = 900;

    /**
     * UploadFile constructor.
     *
     * @param $sourcePath
     * @param $remotePath
     * @param Connection $connection
     */
    public function __construct($sourcePath, $remotePath, Connection $connection)
    {
        $this->sourcePath = $sourcePath;
        $this->remotePath = $remotePath;
        $this->connection = $connection;
    }

    /**
     *
     */
    public function handle()
    {
        $this->connection->setJob($this);
        $this->connection->upload($this->sourcePath, $this->remotePath, false);
    }
}