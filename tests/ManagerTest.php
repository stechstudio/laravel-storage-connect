<?php
namespace STS\StorageConnect\Tests;

use StorageConnect;

class ManagerTest extends TestCase
{
    public function testDefaultDriver()
    {
        config(['storage-connect.default' => 'google']);

        $this->assertEquals('google', StorageConnect::getDefaultDriver());
    }

    public function testInvalidDriver()
    {
        // Without setting up first, this is not a valid driver
        $this->expectException(\InvalidArgumentException::class);
        StorageConnect::verifyDriver("dropbox");
    }

    public function testValidDriver()
    {
        // This setups up the services config
        $this->setupDropbox();

        $this->assertTrue(StorageConnect::verifyDriver("dropbox"));

        // But we can still disable in our package config
        config(['storage-connect.enabled' => []]);

        $this->expectException(\InvalidArgumentException::class);
        StorageConnect::verifyDriver("dropbox");
    }


}