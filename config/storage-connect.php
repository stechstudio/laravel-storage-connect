<?php
return [
    /**
     * This is used as the first level directory in Google Drive. You can also provide the app name
     * using the static property on the StorageConnect instance. If neither is provided, we'll
     * use the Laravel APP_NAME
     */
    'app_name' => env('STORAGE_CONNECT_APP_NAME'),

    /**
     * By default your current domain is used for the callback. However if your app runs on multiple
     * domains/aliases, you may need to explicitly set here the domain you have pre-registered
     * in your Dropbox/Google app.
     */
    'callback_domain' => env('STORAGE_CONNECT_CALLBACK_DOMAIN'),

    /**
     * By default we don't log anything directly, we just fire off events. If you'd like us to
     * log just turn this on.
     */
    'log_activity' => env('STORAGE_CONNECT_LOG_ACTIVITY', false),

    /**
     * Default driver use anytime none is explicitly specified.
     */
    'default' => env('STORAGE_CONNECT_DEFAULT','dropbox'),

    /**
     * Pre-wire authorization routes. Disable this if you want to create your own routes to kick off OAuth.
     */
    'authorize_route' => env('STORAGE_CONNECT_AUTHORIZE_ROUTE', true),

    /**
     * The base URI path we'll register for your authorize/callback endpoints
     */
    'path' => env('STORAGE_CONNECT_PATH', 'storage-connect'),

    /**
     * Specify one or more individual middlewares to be used on the authorize/callback
     * endpoints, or a middleware group. Make sure Illuminate\Session\Middleware\StartSession
     * is one of the middlewares included, that's used for CSRF during the oauth flow.
     */
    'middleware' => env('STORAGE_CONNECT_MIDDLEWARE', 'web'),

    /**
     * This is used as the default redirect location after a successful oauth flow and
     * storage connection. This can be overridden by passing a redirect URL when calling
     * $model->dropbox_connection->connect($redirectUrl) or StorageConnect::authorize($redirectUrl)
     */
    'redirect_after_connect' => env('STORAGE_CONNECT_REDIRECT_AFTER_CONNECT', '/'),

    /**
     * We'll only consider these truly enabled if they are setup in the Laravel 'services'
     * config file. However if you ever need the 'services' file configured for one of these
     * providers for some other use, and want to disable it fo StorageConnect, remove it from
     * this array.
     */
    'enabled' => ['dropbox', 'google'],
];