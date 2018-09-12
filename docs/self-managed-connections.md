# Self-Managed Connections

Sometimes you may not want the connections managed through Eloquent. Perhaps you only need a single cloud storage connection, and it's not tied to any particular user in the database.

In this case you can pass custom callbacks to handle all the loading and saving of the connection.

## Provide callbacks

In your AppServiceProvider boot method tell StorageConnect how to load and save connections.

```php
StorageConnect::saveUsing(function($connection, $provider) {
    // Store the connection wherever you want 
    Storage::put($provider . '_connection.json', $connection);
});

StorageConnect::loadUsing(function($provider) {
    return Storage::get($provider . '_connection.json');
});
```

## Setup a new connection

This package provides a pre-wired route to setup a cloud storage connection. This route is available at `/storage-connect/authorize/dropbox` by default. (Change 'dropbox' to any supported provider name.) This will go through the OAuth flow, and end up calling your save callback when finished.

Of course you can create your own route if need be. First access the driver and then call the authorize method:

```php
Route::get('/my-endpoint', function() {
    return StorageConnect::connection('dropbox')->authorize();
});
```

## Redirect when finished

Both authorization methods above provide a means of specifying the final redirect location once OAuth is complete. For the pre-wired endpoint include a redirect GET parameter:

```
/storage-connect/authorize/dropbox?redirect=/dashboard
```

If you are calling the authorize method simply pass your redirect URL as an argument.

```php
return StorageConnect::connection('dropbox')->authorize('/dashboard');
```

If you don't provide a redirect URL at all the config redirect_after_connect value will be used.

## Load existing connection

You can load a storage connection by using the load method on the StorageConnect facade.

```php
StorageConnect::load('dropbox')->upload(...);
```