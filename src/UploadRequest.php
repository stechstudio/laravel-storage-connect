<?php
namespace STS\StorageConnect;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use STS\StorageConnect\Contracts\UploadTarget;

class UploadRequest
{
    use SerializesModels;

    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * @var string
     */
    protected $destinationPath;

    /**
     * @var Model
     */
    protected $target;

    /**
     * UploadRequest constructor.
     *
     * @param mixed $source
     * @param null $destinationPath
     */
    public function __construct($source, $destinationPath = null)
    {
        if($source instanceof Model && $source instanceof UploadTarget) {
            $this->sourcePath = $source->upload_source_path;
            $this->destinationPath = $destinationPath ?: $source->upload_destination_path;
            $this->target = $source;
        } else {
            $this->sourcePath = $source;
            $this->destinationPath = $destinationPath;
        }

        $this->destinationPath = str_start($this->destinationPath, "/");

        if (starts_with($this->sourcePath, "s3://")) {
            app('aws')->createClient('s3')->registerStreamWrapper();
        }
    }

    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * @return string
     */
    public function getDestinationPath()
    {
        return $this->destinationPath;
    }

    /**
     * @return Model
     */
    public function getTarget()
    {
        return $this->target;
    }
}