<?php
namespace STS\StorageConnect\Contracts;

interface UploadTarget
{
    public function getUploadSourcePathAttribute();
    public function getUploadDestinationPathAttribute();
}