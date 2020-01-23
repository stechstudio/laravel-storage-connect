<?php
namespace STS\StorageConnect\Tests;

use Google_Service_Drive;
use STS\StorageConnect\Drivers\Google\Adapter;
use STS\StorageConnect\Drivers\Google\Provider;
use STS\StorageConnect\StorageConnectFacade;

class GoogleTest extends DriverTestCase
{
    protected $driver = "google";

    protected $adapterClass = Adapter::class;
    protected $providerClass = Provider::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupGoogle();
    }

    public function testService()
    {
        $adapter = StorageConnectFacade::adapter($this->driver);

        $adapter->setToken('{"access_token": "' . $this->token . '", "expires_in": 100000, "created": "' . time() . '"}');
        $service = $adapter->service();

        $this->assertInstanceOf(Google_Service_Drive::class, $service);
    }

    protected function mockProvider()
    {
        $provider = mock(Provider::class);

        $provider->shouldReceive('user')->andReturn(
            new \ArrayObject([
                'accessTokenResponseBody' => $this->token,
                'name' => 'Somebody',
                'email' => 'somebody@somewhere.com'
            ], \ArrayObject::ARRAY_AS_PROPS)
        );

        return $provider;
    }

    protected function mockService()
    {
        $service = mock(Google_Service_Drive::class);
        $service->about = new TestGoogleServiceAbout();

        return $service;
    }
}

class TestGoogleServiceAbout {
    public function get()
    {
        return $this;
    }

    public function getQuotaBytesTotal()
    {
        return 1000000000;
    }

    public function getQuotaBytesUsed()
    {
        return 314159265;
    }
}