<?php
namespace STS\StorageConnect\Providers;

use Illuminate\Http\RedirectResponse;
use STS\StorageConnect\Connections\AbstractConnection;

/**
 * Interface ProviderContract
 * @package STS\StorageConnect\Providers
 */
interface ProviderContract
{
    /**
     * @return RedirectResponse
     */
    public function finish();

    /**
     * @return string
     */
    public function name();

    /**
     * @return AbstractConnection
     */
    public function connection();
}