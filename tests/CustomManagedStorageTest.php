<?php
namespace STS\StorageConnect\Tests;

use Illuminate\Http\RedirectResponse;
use StorageConnect;
use STS\StorageConnect\Models\CustomManagedCloudStorage;

class CustomManagedStorageTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setupDropbox();
    }

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

    public function testSave()
    {
        $saved = json_encode(["id" => 5, "driver" => "foo"]);

        StorageConnect::loadUsing(function() use(&$saved) {
            return $saved;
        });

        StorageConnect::saveUsing(function($storage) use(&$saved) {
            $saved = $storage;
        });

        $this->assertEquals("foo", StorageConnect::driver()->driver);

        StorageConnect::driver()->id = 10;
        StorageConnect::driver()->save();

        $this->assertEquals(10, json_decode($saved, true)['id']);
    }

    public function testPassthrough()
    {
        StorageConnect::loadUsing(function () {
            return null;
        });

        // This will load our custom managed default driver
        $this->assertFalse(StorageConnect::isEnabled());
    }

    public function testAuthorize()
    {
        StorageConnect::loadUsing(function () {});
        StorageConnect::saveUsing(function () {});

        $this->setupDropbox();

        $response = StorageConnect::authorize();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session('storage-connect.custom'));
    }

    public function testOwner()
    {
        StorageConnect::loadUsing(function () {
            return [
                'driver' => 'dropbox',
                'name' => "Someone",
                'email' => 'someone@somewhere.com'
            ];
        });

        $this->assertEquals('someone@somewhere.com', StorageConnect::getAttribute('owner_description'));
    }
}