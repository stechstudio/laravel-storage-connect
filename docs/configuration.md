# Configuration

Most of the package configuration can be done using your .env file, which is recommended.

If you want to edit the config file directly, first publish it to your own config directory using `php artisan vendor:publish`.

## Default provider

```
STORAGE_CONNECT_DEFAULT=dropbox
```

This is used if you are managing connections yourself and make direct calls to the StorageConnect facade without specifying a provider.

```php
StorageConnect::upload("/path/to/source.pdf","filename.pdf");
```

This will use the default provider, load the storage from your custom callback, and then proceed to upload.

## Application name

```
STORAGE_CONNECT_APP_NAME=My fancy app name
```

This is used for the root folder name in Google Drive. If you don't provide this we'll use the Laravel `APP_NAME`.

## Log activity

```
STORAGE_CONNECT_LOG_ACTIVITY=false
```

By default, this package does not do any logging. Instead it fires events and lets you choose how to handle them.

If you'd like activity logging, set this to true.

## Authorize routes

```
STORAGE_CONNECT_AUTHORIZE_ROUTE=true
```

By default this package create a pre-wired route for kicking off the OAuth flow.

Disable this flag if you want to setup your own routes and avoid this completely.

## Route middleware

```
STORAGE_CONNECT_MIDDLEWARE=web
```

The pre-wired authorize route uses the `web` middleware group by default. 

You can change this by specifying a middleware name (or group name) here. If you change this make sure your middleware group includes `session` so that CSRF is handled during the OAuth flow.

## Redirecting after OAuth

```
STORAGE_CONNECT_REDIRECT_AFTER_CONNECT=/dashboard
```

You can specify a redirect URL when using the pre-wired authorize route or when calling the authorize method, yourself, on a connection.

If a redirect URL is not explicitly passed in, this config will be used as a fallback.
