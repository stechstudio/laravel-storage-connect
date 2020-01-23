<?php

namespace STS\StorageConnect\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Contracts\UploadTarget;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadInProgress;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\UploadStarted;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Jobs\CheckUploadStatus;
use STS\StorageConnect\Jobs\UploadFile;
use STS\StorageConnect\UploadRequest;
use STS\StorageConnect\UploadResponse;

/**
 * @property Carbon uploaded_at
 */
trait UploadsFiles
{
    /**
     * @param      $source
     * @param      $destinationPath
     * @param bool $shouldQueue
     * @param null $queueJob
     *
     * @return UploadResponse|bool
     */
    public function upload($source, $destinationPath = null, $shouldQueue = true, $queueJob = null)
    {
        $this->verify();

        if ($shouldQueue) {
            return dispatch(new UploadFile($source, $destinationPath, $this));
        }

        $request = new UploadRequest($source, $destinationPath);

        try {
            return $this->handleUpload($request);
        } catch (UploadException $exception) {
            $this->handleUploadError($exception, $queueJob);

            return false;
        } finally {
            $this->ping();
        }
    }

    /**
     * @param UploadRequest $request
     *
     * @return UploadResponse
     */
    protected function handleUpload(UploadRequest $request)
    {
        return $this->processResponse($this->adapter()->upload($request));
    }

    /**
     * @param UploadResponse $response
     * @param $queueJob
     */
    public function checkUploadStatus(UploadResponse $response, $queueJob)
    {
        $response->incrementStatusCheck();

        try {
            $this->processResponse($this->adapter()->checkUploadStatus($response));
        } catch (UploadException $exception) {
            if($exception->shouldRetry()) {
                $queueJob->release();
            } else {
                $queueJob->fail($exception);
                event(new UploadFailed($this, $exception));
            }

            if ($exception->shouldDisable()) {
                $this->disable($exception->getReason());
            }
        }
    }

    /**
     * @param UploadResponse $response
     *
     * @return UploadResponse
     */
    protected function processResponse(UploadResponse $response)
    {
        if ($response->isAsync()) {
            dispatch(new CheckUploadStatus($this, $response))->delay(15);

            if ($response->getStatusChecks() == 0) {
                event(new UploadStarted($this, $response));
            } else {
                event(new UploadInProgress($this, $response));
            }
        } else {
            $this->uploaded_at = Carbon::now();
            $this->save();

            event(new UploadSucceeded($this, $response));
        }

        return $response;
    }

    /**
     * @param UploadException $exception
     * @param null $job
     *
     * @return mixed
     */
    protected function handleUploadError(UploadException $exception, $job = null)
    {
        $exception->setStorage($this);

        if ($exception->shouldRetry() && $job) {
            event(new UploadRetrying($this, $exception));

            $job->release();

            return;
        }

        if ($exception->shouldDisable()) {
            $this->disable($exception->getReason());
        }

        if ($job) {
            $job->fail($exception);
        }

        event(new UploadFailed($this, $exception));
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isUploaded($path)
    {
        if($path instanceof Model && $path instanceof UploadTarget) {
            $path = $path->upload_destination_path;
        }

        return $this->adapter()->pathExists(
            str_start($path, '/')
        );
    }
}