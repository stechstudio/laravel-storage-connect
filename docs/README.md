# Introduction

This is a Laravel package designed to drastically simplify the process of authenticating to a user's cloud storage account and uploading files.

- Handles OAuth flow to authorize a cloud storage account
- Serializes and handles storing/loading of access tokens and connection details
- Queues upload tasks
- Retries upload failures with increasing backoff
- Supports uploading from local filesystem, S3 hosted files (registers a stream wrapper), or any URL
- Automatically disables a connection if storage quota is full, and then re-enables if space is freed up
- Fires events for all activity, and can optionally log activity for you

## Quick example

There are two primary ways to work with cloud storage connections.

### 1. Using connections through eloquent

Let's say you want to connect to the Dropbox account of your currently logged-in user. You would create a route with this code:

```php
return Auth::user()->dropbox_connection->authorize($redirectUrl);
```

This will redirect the user through the Dropbox oauth process, handle the callback, store the access tokens in the database for your user, and finally redirect back to your desired page.

Now later on when you want to upload a file to this cloud storage:

```php
Auth::user()->dropbox_connection->upload("/path/to/source/file.pdf", "uploaded.pdf");
```

This creates a queued job to upload the file, retry on error, and will dispatch an event when it succeeds or if it ultimately fails.

### 2. Using without Eloquent

If you aren't looking to link the cloud storage connection to a database model (perhaps you are only looking to configure one connection for your whole app) you can also provide your own load/save methods.

Stick these in your AppServiceProvider boot method:

```php
StorageConnect::saveUsing(function($connection, $driver) {
    // Store the connection wherever you want 
    Storage::put($driver . '_connection.json', $connection);
});

StorageConnect::loadUsing(function($driver) {
    return Storage::get($driver . '_connection.json');
});
```

To authorize a new cloud connection you can redirect to the pre-wired route of /storage-connect/authorize/dropbox, or you can create our own route and call:

```php
return StorageConnect::driver('dropbox')->authorize($redirectUrl);
```

Once authorized you can load a connection and upload files:

```php
StorageConnect::load('dropbox')->upload("/path/to/source/file.pdf", "uploaded.pdf");
```