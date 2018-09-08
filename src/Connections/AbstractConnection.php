<?php
namespace STS\StorageConnect\Connections;

use STS\StorageConnect\Providers\DropboxProvider;
use STS\StorageConnect\Providers\ProviderContract;

/**
 * Class AbstractConnection
 * @package STS\StorageConnect\Connections
 */
abstract class AbstractConnection
{
    /**
     * @var
     */
    protected $owner;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var DropboxProvider
     */
    protected $provider;

    /**
     * AbstractConnection constructor.
     *
     * @param $provider
     */
    public function __construct( $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param $owner
     *
     * @return $this
     */
    public function belongsTo( $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return mixed
     */
    public function driver()
    {
        if(!$this->provider) {
            $this->provider = $this->manager->driver($this->name);
        }

        return $this->provider;
    }

    /**
     * @param $config
     *
     * @return $this
     */
    public function unserialize( $config)
    {
        return $this->load(json_decode($config, true));
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function load(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->config);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function __get( $key)
    {
        return array_get($this->config, $key);
    }
}