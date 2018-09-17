<?php
namespace STS\StorageConnect\Tests;

use STS\StorageConnect\Traits\ConnectsToCloudStorage;

class TestUser extends \Illuminate\Database\Eloquent\Model
{
    use ConnectsToCloudStorage;

    protected $table = "users";

    protected $guarded = [];
}