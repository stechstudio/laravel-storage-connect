<?php
namespace STS\StorageConnect\Tests;

use StorageConnect;
use STS\StorageConnect\Models\CustomManagedCloudStorage;

class CustomManagedStorageTest extends TestCase
{
    public function testMissingLoadCallback()
    {
        $this->expectException(\UnexpectedValueException::class);
        StorageConnect::driver();
    }

    public function testCreateNew()
    {
        StorageConnect::loadUsing(function () {
            return null;
        });

        $this->assertInstanceOf(CustomManagedCloudStorage::class, StorageConnect::driver());
        $this->assertFalse(StorageConnect::driver()->exists);
    }

    public function testLoadExisting()
    {
        StorageConnect::loadUsing(function() {
            return ["id" => 5, "driver" => "foo"];
        });

        $this->assertInstanceOf(CustomManagedCloudStorage::class, StorageConnect::driver());
        $this->assertEquals("foo", StorageConnect::driver()->driver);
        $this->assertTrue(StorageConnect::driver()->exists);
    }
}