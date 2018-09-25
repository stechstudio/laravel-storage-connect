<?php
namespace STS\StorageConnect\Tests;

use STS\StorageConnect\Contracts\UploadTarget;

class TestFile extends \Illuminate\Database\Eloquent\Model implements UploadTarget
{
    protected $table = "files";

    protected $guarded = [];

    public function getUploadSourcePathAttribute()
    {
        return $this->attributes['source_path'];
    }

    public function getUploadDestinationPathAttribute()
    {
        return $this->attributes['destination_path'];
    }
}