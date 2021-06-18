<?php
namespace STS\StorageConnect\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $source;
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
     * @param $source
     * @param $destinationPath
     * @param CloudStorage $storage
     */
    public function __construct($source, $destinationPath, CloudStorage $storage)
    {
        $this->source = $source;
        $this->destinationPath = $destinationPath;
        $this->storage = $storage;
    }

    /**
     *
     * @throws \STS\StorageConnect\Exceptions\StorageUnavailableException
     */
    public function handle()
    {
        $this->storage->upload($this->source, $this->destinationPath, false, $this);
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
