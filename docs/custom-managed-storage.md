# Custom Managed Storage

Perhaps it doesn't make sense for your app to manage storage connections from an Eloquent model. Maybe you aren't setting up storage for multiple users or organizations, but rather one single storage connection for your whole app.

In this case you can bypass the entire Eloquent workflow and provide two callbacks for saving and loading a storage connection.

## Provide callbacks

In your AppServiceProvider boot method tell StorageConnect how to load and save:

```php
StorageConnect::saveUsing(function($storage, $driver) {
    // Store the connection wherever you want 
    Storage::put($driver . '_connection.json', $storage);
});

StorageConnect::loadUsing(function($driver) {
    return Storage::get($driver . '_connection.json');
});
```

## Authorize cloud storage

You will need to setup a route for the OAuth flow. You can optionally provide a final redirect location.

```php
Route::get('/my-authorize-endpoint', function() {
    return StorageConnect::driver('dropbox')->authorize("/dashboard");
});
```

This will take the your through the OAuth flow, create the cloud storage connection, save it using your custom callback, and finally redirect to `/dashboard` when finished.

If no redirect is provided, the final redirect will be used from your [configuration setting](configuration.html#redirecting-after-oauth).

## Upload files

You can load your cloud storage connection by using the `driver` method on the StorageConnect facade:

```php
StorageConnect::driver('dropbox')->upload(...);
```