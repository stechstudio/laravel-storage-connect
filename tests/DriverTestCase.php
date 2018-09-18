<?php

namespace STS\StorageConnect\Tests;

use Laravel\Socialite\Two\AbstractProvider;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Events\CloudStorageSetup;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\StorageConnectFacade;
use STS\StorageConnect\StorageConnectManager;
use STS\StorageConnect\Types\Quota;
use Event;

abstract class DriverTestCase extends TestCase
{
    protected $driver;

    protected $token = "ABCDEFG";

    public function testAdapter()
    {
        $adapter = StorageConnectFacade::adapter($this->driver);

        $this->assertInstanceOf(AbstractAdapter::class, $adapter);
        $this->assertEquals($this->driver, $adapter->driver());
    }

    public function testProvider()
    {
        $provider = StorageConnectFacade::adapter($this->driver)->provider();

        $this->assertInstanceOf(AbstractProvider::class, $provider);
    }

    abstract public function testService();

    public function testQuota()
    {
        $adapter = StorageConnectFacade::adapter($this->driver);

        $adapter->setService($this->mockService());
        /** @var Quota $quota */
        $quota = $adapter->getQuota();

        $this->assertInstanceOf(Quota::class, $quota);
        $this->assertEquals(314159265, $quota->getUsed());
        $this->assertEquals(1000000000, $quota->getTotal());
        $this->assertEquals(31.4, $quota->getPercentFull());
    }

    public function testAuthFlow()
    {
        Event::fake();

        $storage = factory(TestUser::class)->create()->getCloudStorage($this->driver);

        $storage->adapter()->setService($this->mockService());
        $response = $storage->authorize('/final-redirect');

        $query = parse_url($response->getTargetUrl())['query'];

        $storage->adapter()->setProvider($this->mockProvider());
        $manager = new TestManager(app());
        $manager->setAdapter($storage->adapter());

        $manager->finish($this->driver);

        /** @var CloudStorage $storage */
        $storage = $storage->fresh();

        $this->assertTrue($storage->isConnected());
        $this->assertEquals($this->token, $storage->token);
        $this->assertEquals(31.4, $storage->percentFull());

        Event::assertDispatched(CloudStorageSetup::class);
    }
}

// Because sometimes creating an extended test dummy to replace one method (and leave the rest of the instance in tact)
// is easier than mocking...
class TestManager extends StorageConnectManager
{
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    public function adapter($driver)
    {
        return $this->adapter;
    }
}