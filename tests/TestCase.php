<?php
namespace STS\StorageConnect\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use STS\StorageConnect\StorageConnectFacade;
use STS\StorageConnect\StorageConnectServiceProvider;
use Mockery;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testbench']);
        $this->loadLaravelMigrations(['--database' => 'testbench']);

        $factory = app(\Illuminate\Database\Eloquent\Factory::class);
        require(__DIR__ . "/ModelFactory.php");

        Mockery::globalHelpers();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            StorageConnectServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'StorageConnect' => StorageConnectFacade::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['request']->setLaravelSession($app['session']->driver('array'));
    }

    /**
     *
     */
    protected function setupDropbox()
    {
        config([
            'services.dropbox.client_id' => 'foo',
            'services.dropbox.client_secret' => 'bar'
        ]);
    }

    protected function setupGoogle()
    {
        config([
            'services.google.client_id' => 'foo',
            'services.google.client_secret' => 'bar'
        ]);
    }
}