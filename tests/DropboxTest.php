<?php
namespace STS\StorageConnect\Tests;

use Kunnu\Dropbox\Dropbox;
use STS\StorageConnect\Drivers\Dropbox\Adapter;
use STS\StorageConnect\Drivers\Dropbox\Provider;
use STS\StorageConnect\StorageConnectFacade;

class DropboxTest extends DriverTestCase
{
    protected $driver = "dropbox";

    protected $adapterClass = Adapter::class;
    protected $providerClass = Provider::class;

    protected function setUp()
    {
        parent::setUp();

        $this->setupDropbox();
    }

    public function testService()
    {
        $service = StorageConnectFacade::adapter($this->driver)->service();

        $this->assertInstanceOf(Dropbox::class, $service);
    }

    protected function mockProvider()
    {
        $provider = mock(Provider::class);

        $provider->shouldReceive('user')->andReturn(
            new \ArrayObject([
                'accessTokenResponseBody' => $this->token,
                'user' => [
                    'name' => [
                        'display_name' => 'Somebody',
                    ],
                    'email' => 'somebody@somewhere.com'
                ]
            ], \ArrayObject::ARRAY_AS_PROPS)
        );

        return $provider;
    }

    protected function mockService()
    {
        $service = mock(Dropbox::class);

        $service->shouldReceive('getSpaceUsage')->andReturn([
            "used" => 314159265,
            "allocation" => [
                ".tag" => "individual",
                "allocated" => 1000000000
            ]
        ]);



        return $service;
    }
}