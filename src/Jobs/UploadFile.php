<?php
namespace STS\StorageConnect\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use STS\Backoff\Backoff;
use STS\Backoff\Strategies\PolynomialStrategy;
use STS\StorageConnect\Models\CloudStorage;

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
    protected $destinationPath;
    /**
     * @var CloudStorage
     */
    protected $storage;

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
     * @param $destinationPath
     * @param CloudStorage $storage
     */
    public function __construct($sourcePath, $destinationPath, CloudStorage $storage)
    {
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
        $this->storage = $storage;
    }

    /**
     *
     * @throws \STS\StorageConnect\Exceptions\StorageUnavailableException
     */
    public function handle()
    {
        $this->storage->setJob($this);
        $this->storage->upload($this->sourcePath, $this->destinationPath, false);
    }

    /**
     *
     */
    public function release()
    {
        if(!$this->job) {
            return;
        }

        $this->job->release(
            (new Backoff)
                ->setStrategy(new PolynomialStrategy(5, 3))
                ->setWaitCap(900)
                ->setJitter(true)
                ->getWaitTime($this->job->attempts())
        );
    }
}