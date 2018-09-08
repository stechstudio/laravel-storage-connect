<?php
namespace STS\StorageConnect\Providers;

use Illuminate\Http\RedirectResponse;
use SocialiteProviders\Manager\OAuth2\User;

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
     * @return string
     */
    public function serialize();
}