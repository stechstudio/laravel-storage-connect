<?php
namespace STS\StorageConnect\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use STS\StorageConnect\Connections\AbstractConnection;

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
     * @var AbstractConnection
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
     * @param AbstractConnection $connection
     */
    public function __construct( $sourcePath, $remotePath, AbstractConnection $connection)
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