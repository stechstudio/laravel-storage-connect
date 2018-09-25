<?php
namespace STS\StorageConnect\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Contracts\UploadTarget;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Jobs\UploadFile;

trait UploadsFiles
{
    /**
     * @param      $source
     * @param      $destinationPath
     * @param bool $shouldQueue
     * @param null $queueJob
     *
     * @return bool
     */
    public function upload($source, $destinationPath = null, $shouldQueue = true, $queueJob = null)
    {
        $this->verify();

        if ($shouldQueue) {
            return dispatch(new UploadFile($source, $destinationPath, $this));
        }

        list($sourcePath, $destinationPath, $targetModel) = $this->preparePaths($source, $destinationPath);

        try {
            return $this->handleUpload($sourcePath, $destinationPath, $targetModel);
        } catch (UploadException $exception) {
            $this->handleUploadError($exception, $queueJob, $targetModel);
        }

        $this->ping();

        return false;
    }

    /**
     * @param $source
     * @param null $destinationPath
     *
     * @return array
     */
    protected function preparePaths($source, $destinationPath = null)
    {
        if($source instanceof Model && $source instanceof UploadTarget) {
            return [
                $source->upload_source_path,
                $destinationPath ?: $source->upload_destination_path,
                $source
            ];
        }

        return [
            (string) $source,
            $destinationPath,
            null
        ];
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     * @param null $targetModel
     *
     * @return bool
     */
    protected function handleUpload($sourcePath, $destinationPath, $targetModel = null)
    {
        if (starts_with($sourcePath, "s3://")) {
            app('aws')->createClient('s3')->registerStreamWrapper();
        }

        $this->adapter()->upload($sourcePath, $destinationPath);

        $this->uploaded_at = Carbon::now();
        $this->save();

        event(new UploadSucceeded($this, $sourcePath, $destinationPath, $targetModel));

        return true;
    }

    /**
     * @param UploadException $exception
     * @param null $job
     * @param null $targetModel
     *
     * @return mixed
     */
    protected function handleUploadError(UploadException $exception, $job = null, $targetModel = null)
    {
        $exception->setStorage($this);

        if ($exception->shouldRetry() && $job) {
            event(new UploadRetrying($this, $exception, $targetModel));

            $job->release();

            return;
        }

        if ($exception->shouldDisable()) {
            $this->disable($exception->getReason());
        }

        if ($job) {
            $job->fail($exception);
        }

        event(new UploadFailed($this, $exception, $targetModel));
    }
}