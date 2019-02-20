<?php

namespace STS\StorageConnect\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\UploadResponse;

/**
 *
 */
class CheckUploadStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var CloudStorage
     */
    protected $storage;

    /**
     * @var UploadResponse
     */
    protected $response;

    /**
     * CheckUploadStatus constructor.
     *
     * @param CloudStorage $storage
     * @param UploadResponse $response
     */
    public function __construct(CloudStorage $storage, UploadResponse $response)
    {
        $this->storage = $storage;
        $this->response = $response;
        $this->delay = $response->getNextCheckDelay();
    }

    /**
     *
     */
    public function handle()
    {
        $this->storage->checkUploadStatus($this->response, $this);
    }

    /**
     *
     */
    public function release()
    {
        $this->job->release($this->response->getNextCheckDelay());
    }
}